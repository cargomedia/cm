<ul class="tabs menu-tabs example-navigation">
  {block name="tabs"}
    <li data-tab="components"><a href="{linkUrl page='CM_Page_Example' tab='components'}">Components</a></li>
    <li data-tab="menus"><a href="{linkUrl page='CM_Page_Example' tab='menus'}">Menus</a></li>
    <li data-tab="button"><a href="{linkUrl page='CM_Page_Example' tab='button'}">Buttons</a></li>
    <li data-tab="forms"><a href="{linkUrl page='CM_Page_Example' tab='forms'}">Forms</a></li>
    <li data-tab="variables"><a href="{linkUrl page='CM_Page_Example' tab='variables'}">Variables</a></li>
    <li data-tab="icons"><a href="{linkUrl page='CM_Page_Example' tab='icons'}">Icons</a></li>
  {/block}
</ul>

<div class="tabs-content">
  {block name="tabs-content"}
    <div>
      {componentTemplate file='tabs/example.tpl' foo=$foo now=$now}
    </div>
    <div>
      {code language="html5"}{load file='Component/Example/tabs/menus.tpl' namespace='CM' parse=false}{/code}
      {componentTemplate file='tabs/menus.tpl'}
    </div>
    <div>
      {code language="html5"}{load file='Component/Example/tabs/buttons.tpl' namespace='CM' parse=false}{/code}
      {componentTemplate file='tabs/buttons.tpl'}
    </div>
    <div>
      {code language="html5"}{load file='Component/Example/tabs/forms.tpl' namespace='CM' parse=false}{/code}
      {componentTemplate file='tabs/forms.tpl'}
    </div>
    <div>
      {componentTemplate file='tabs/variables.tpl' colorStyles=$colorStyles}
    </div>
    <div>
      {componentTemplate file='tabs/icons.tpl' icons=$icons}
    </div>
  {/block}
</div>
