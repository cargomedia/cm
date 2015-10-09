<ul class="tabs menu menu-tabs example-navigation">
  {block name="tabs"}
    <li data-tab="components"><a href="{linkUrl page='CM_Page_Example' tab='components'}" class="clickFeedback">Components</a></li>
    <li data-tab="menus"><a href="{linkUrl page='CM_Page_Example' tab='menus'}" class="clickFeedback">Menus</a></li>
    <li data-tab="button"><a href="{linkUrl page='CM_Page_Example' tab='button'}" class="clickFeedback">Buttons</a></li>
    <li data-tab="forms"><a href="{linkUrl page='CM_Page_Example' tab='forms'}" class="clickFeedback">Forms</a></li>
    <li data-tab="variables"><a href="{linkUrl page='CM_Page_Example' tab='variables'}" class="clickFeedback">Variables</a></li>
    <li data-tab="icons"><a href="{linkUrl page='CM_Page_Example' tab='icons'}" class="clickFeedback">Icons</a></li>
    <li data-tab="styleguide"><a href="{linkUrl page='CM_Page_Example' tab='styleguide'}" class="clickFeedback">Styleguide</a></li>
  {/block}
</ul>

<div class="tabs-content">
  {block name="tabs-content"}
    <div>
      {viewTemplate name='tabs/example' foo=$foo now=$now}
    </div>
    <div>
      {code language="html5"}{load file='Component/Example/tabs/menus.tpl' namespace='CM' parse=false}{/code}
      {viewTemplate name='tabs/menus'}
    </div>
    <div>
      {code language="html5"}{load file='Component/Example/tabs/buttons.tpl' namespace='CM' parse=false}{/code}
      {viewTemplate name='tabs/buttons'}
    </div>
    <div>
      {block name="tabs-forms"}
        <h2>CM_Form_Example</h2>
        {code language="html5"}{load file='Component/Example/tabs/forms.tpl' namespace='CM' parse=false}{/code}
        {viewTemplate name='tabs/forms'}
      {/block}
    </div>
    <div>
      {viewTemplate name='tabs/variables' colorStyles=$colorStyles}
    </div>
    <div>
      {viewTemplate name='tabs/icons' icons=$icons}
    </div>
    <div>
      {viewTemplate name='tabs/styleguide'}
    </div>
  {/block}
</div>
