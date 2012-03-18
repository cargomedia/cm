<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width">
		<meta name="description" content="{block name='description'}{/block}">
		<meta name="keywords" content="{block name='keywords'}{/block}">
		<title>{block name='title'}{$page->getTitle()|escape}{/block}</title>
		{resource file='library.css'}
		{resource file='internal.css'}
		{resource file='init.js'}
		{block name='head'}{/block}
	</head>
	<body>
		{block name='body-start'}{/block}
		{block name='content'}{/block}
		{resource file='library.js'}
		{resource file='internal.js'}
		{$js->renderScripts()}
		{CM_Tracking::getInstance()->getHtml()}
		{block name='body-end'}{/block}
	</body>
</html>
