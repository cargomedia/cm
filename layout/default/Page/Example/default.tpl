{extends file=$render->getLayoutPath('Page/Abstract/default.tpl')}

{block name='content-main'}
  {component name='CM_Component_Example' site=$render->getSite()}
{/block}
