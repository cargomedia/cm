<ul class="tabs menu-tabs">
	<li><a href="javascript:;">Components</a></li>
	<li><a href="javascript:;">Menus</a></li>
	<li><a href="javascript:;">Buttons</a></li>
	<li><a href="javascript:;">Forms</a></li>
	<li><a href="javascript:;">Variables</a></li>
	<li><a href="javascript:;">Icons</a></li>
</ul>

<div class="tabs-content">
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
</div>
