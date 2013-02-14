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
	{load file='Component/Example/tabs/example.tpl' namespace='CM'}
	</div>

	<div>
	{code language="html5"}{load file='Component/Example/tabs/menus.tpl' namespace='CM' parse=false}{/code}
		{load file='Component/Example/tabs/menus.tpl' namespace='CM'}
	</div>

	<div>
		{code language="html5"}{load file='Component/Example/tabs/buttons.tpl' namespace='CM' parse=false}{/code}
		{load file='Component/Example/tabs/buttons.tpl' namespace='CM'}
	</div>

	<div>
		{code language="html5"}{load file='Component/Example/tabs/forms.tpl' namespace='CM' parse=false}{/code}
		{load file='Component/Example/tabs/forms.tpl' namespace='CM'}
	</div>

	<div>
	{load file='Component/Example/tabs/variables.tpl' namespace='CM'}
	</div>

	<div>
		{load file='Component/Example/tabs/icons.tpl' namespace='CM'}
	</div>
</div>
