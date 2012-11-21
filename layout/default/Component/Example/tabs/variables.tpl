<h3>Colors</h3>
<ul class="colorList">
	{foreach $colorStyles as $color => $style}
		<li><span class="colorBox" style="{$style}"></span>@{$color}</li>
	{/foreach}
</ul>
