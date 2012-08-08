<h2>{translate 'Icons'}</h2>
<table>
	<tr>
		<td>
			<div class="clearfix">
				{foreach $icons as $icon}
					<div class="iconBox">
						<span class="icon {$icon}"></span>
						<span class="label">{$icon}</span>
					</div>
				{/foreach}
			</div>
		</td>
		<td>
			<h3>Markup</h3>
			{code language="html"}<span class="icon [icon]"></span>{/code}
			<h3>Custom Style Options</h3>
				{code language="less"}
					font-size, color, text-shadow
				{/code}
			<h3>Icon Style Generator</h3>
			{form name="CM_Form_ExampleIcon"}
				{formField name='sizeSlider' label="{translate 'Size'}"}
				{formField name='colorBackground' label="{translate 'Background'}"}
				{formField name='color' label="{translate 'Color'}"}
				{formField name='shadowColor' label="{translate 'Shadow Color'}"}
				{formField name='shadowX' label="{translate 'Shadow X'}"}
				{formField name='shadowY' label="{translate 'Shadow Y'}"}
				{formField name='shadowBlur' label="{translate 'Shadow Blur'}"}
				<h4>Grab Code</h4>
				<code class="iconMarkup html-syntax">Select icon to generate code!</code>
				<br />
				<code class="iconCss less-syntax"></code>
			{/form}
		</td>
	</tr>
</table>