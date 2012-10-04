<?php

function smarty_function_menu(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Page_Abstract $page */
	$page = $render->getStackLast('pages');
	/** @var CM_Model_User $viewer */
	$viewer = $render->getViewer();
	$activePath = $page ? $page::getPath() : '/';
	/** @var CM_Params $activeParams */
	$activeParams = $page ? $page->getParams() : CM_Params::factory();

	$menu = null;
	if (isset($params['name'])) {
		$name = $params['name'];
		$menuArr = $render->getSite()->getMenus();
		if (isset($menuArr[$name])) {
			$menu = $menuArr[$name];
		}
	} elseif (isset($params['data'])) {
		$menu = new CM_Menu($params['data']);
	}
	if (!$menu) {
		return '';
	}

	$menuEntries = null;

	$depth = isset($params['depth']) ? (int) $params['depth'] : null;

	if (!empty($params['breadcrumb'])) {
		/** @var CM_MenuEntry $entry */
		$entry = $menu->findEntry($page, $depth, $depth);
		if ($entry) {
			$menuEntries = $entry->getParents();
			// Also add current entry
			$menuEntries[] = $entry;
		}
	} elseif (!empty($params['submenu'])) {
		$entry = $menu->findEntry($page, $depth, $depth);
		if ($entry && $entry->hasChildren()) {
			$menu = $entry->getChildren();
			$menuEntries = $menu->getEntries($viewer);
		}
	} elseif (!is_null($depth)) {
		if ($entry = $menu->findEntry($page, $depth)) {
			$parents = $entry->getParents();
			$parents[] = $entry;
			/** @var CM_MenuEntry $menuEntry */
			$menuEntry = $parents[$depth];
			$menuEntries = $menuEntry->getSiblings()->getEntries($viewer);
		}
	} else {
		$menuEntries = $menu->getEntries($viewer);
	}

	if (empty($menuEntries)) {
		return '';
	}

	$class = 'menu';
	if (isset($params['name'])) {
		$class .= ' ' . $params['name'];
	}
	if (isset($params['class'])) {
		$class .= ' ' . $params['class'];
	}

	$tplName = 'default';
	if (isset($params['template'])) {
		$tplName = $params['template'];
	}

	$tplPath = $render->getLayoutPath('menu/' . $tplName . '.tpl');
	$assign = array('menu_entries' => $menuEntries, 'menu_class' => $class, 'activePath' => $activePath, 'activeParams' => $activeParams);
	return $render->renderTemplate($tplPath, $assign, true);
}
