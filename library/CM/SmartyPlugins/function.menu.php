<?php

function smarty_function_menu(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $environment = $render->getEnvironment();
    $pageViewResponse = $render->getFrontend()->getClosestViewResponse('CM_Page_Abstract');

    $page = null;
    $pageClassName = null;
    $activePath = '/';
    $activeParams = CM_Params::factory();
    if ($pageViewResponse) {
        /** @var CM_Page_Abstract $page */
        $page = $pageViewResponse->getView();
        $pageClassName = get_class($page);
        $activePath = $page::getPath();
        $activeParams = $page->getParams();
    }

    $menu = null;
    $name = null;
    if (isset($params['name'])) {
        $name = $params['name'];
        $menuArr = $render->getSite()->getMenus();
        if (isset($menuArr[$name])) {
            $menu = $menuArr[$name];
        }
    } elseif (isset($params['data'])) {
        $menu = new CM_Menu($params['data']);
        $render->addMenu($menu);
    }
    if (!$menu) {
        return '';
    }

    $menuEntries = null;

    $depth = isset($params['depth']) ? (int) $params['depth'] : null;

    if (!empty($params['breadcrumb'])) {
        /** @var CM_MenuEntry $entry */
        $entry = $menu->findEntry($pageClassName, $activeParams, $depth, $depth);
        if ($entry) {
            $menuEntries = $entry->getParents();
            // Also add current entry
            $menuEntries[] = $entry;
        }
    } elseif (!empty($params['submenu'])) {
        $entry = $menu->findEntry($pageClassName, $activeParams, $depth, $depth);
        if ($entry && $entry->hasChildren()) {
            $menu = $entry->getChildren();
            $menuEntries = $menu->getEntries($environment);
        }
    } elseif (!is_null($depth)) {
        if ($entry = $menu->findEntry($pageClassName, $activeParams, $depth)) {
            $parents = $entry->getParents();
            $parents[] = $entry;
            /** @var CM_MenuEntry $menuEntry */
            $menuEntry = $parents[$depth];
            $menuEntries = $menuEntry->getSiblings()->getEntries($environment);
        }
    } else {
        $menuEntries = $menu->getEntries($environment);
    }

    if (empty($menuEntries)) {
        return '';
    }

    $class = 'menu';
    if ($name) {
        $class .= ' ' . $name;
    }
    if (isset($params['class'])) {
        $class .= ' ' . $params['class'];
    }

    $tplName = 'default';
    if (isset($params['template'])) {
        $tplName = $params['template'];
    }

    $tplPath = $render->getLayoutPath('menu/' . $tplName . '.tpl');
    $assign = array('menu_entries' => $menuEntries, 'menu_class' => $class, 'activePath' => $activePath, 'activeParams' => $activeParams,
                    'name'         => $name);
    return $render->fetchTemplate($tplPath, $assign, true);
}
