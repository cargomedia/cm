<?php

function smarty_function_menu(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Page_Abstract $page */
	$page = $render->getStackLast('pages');
	/** @var CM_Model_User $viewer */
	$viewer = $render->getViewer();
	$path = $page ? $page::getPath() : '';
	/** @var CM_Params $pageParams */
	$pageParams = $page ? $page->getParams() : CM_Params::factory(array());
	$userId = $viewer ? $viewer->getId() : 0;


	$menu = null;
	if (isset($params['name'])) {
		$name = $params['name'];
		$cacheKey = CM_CacheConst::Menu . '_name:' . $name . '_siteId:' . $render->getSite()->getId() . '_userId:' . $userId;
		if (($menu = CM_Cache_Runtime::get($cacheKey)) === false) {
			$menuArr = include $render->getLayoutPath('menu.php', null, true);
			if (isset($menuArr[$name])) {
				$menu = new CM_Menu($menuArr[$name], $path, $pageParams, $viewer);
			}
			CM_Cache_Runtime::set($cacheKey, $menu);
		}
	} elseif (isset($params['data'])) {
		$menu = new CM_Menu($params['data'], $path, $pageParams, $viewer);
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
			$menuEntries = $menu->getEntries();
		}
	} elseif (!is_null($depth)) {
		if ($entry = $menu->findEntry($page, $depth)) {
			$parents = $entry->getParents();
			$parents[] = $entry;
			$menuEntries = $parents[$depth]->getSiblings()->getEntries();
		}
	} else {
		$menuEntries = $menu->getEntries();
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
	$assign = array('menu_entries' => $menuEntries, 'menu_class' => $class);
	return $render->renderTemplate($tplPath, $assign, true);
}
