<h2>{translate 'Variables'}</h2>
<table class="variables">
	<tr>
		<td>
			<h3>Colors</h3>
			<ul>
				{foreach $colorStyles as $color => $style}
					<li><span class="colorBox" style="{$style}"></span>@{$color}</li>
				{/foreach}
			</ul>
		</td>
		<td>

		</td>
	</tr>
</table>