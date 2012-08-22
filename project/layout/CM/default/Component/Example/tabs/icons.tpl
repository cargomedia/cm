<div class="column">
	<h3>Icon Generator</h3>
	{foreach $icons as $icon}
		<div class="iconBox">
			<span class="icon {$icon}"></span>
			<span class="label">{$icon}</span>
		</div>
	{/foreach}
</div>
<div class="column">
	<h3>Icon Generator</h3>
	{form name="CM_Form_ExampleIcon"}
		{formField name='sizeSlider' label="{translate 'Size'}"}
		{formField name='colorBackground' label="{translate 'Background'}"}
		{formField name='color' label="{translate 'Color'}"}
		{formField name='shadowColor' label="{translate 'Shadow Color'}"}
		{formField name='shadowX' label="{translate 'Shadow X'}"}
		{formField name='shadowY' label="{translate 'Shadow Y'}"}
		{formField name='shadowBlur' label="{translate 'Shadow Blur'}"}
		<h4>Grab Code</h4>
		{code language='html5' class='iconMarkup'}Select icon to generate code!{/code}
		{code language='css' class='iconCss'}{/code}
	{/form}
</div>
