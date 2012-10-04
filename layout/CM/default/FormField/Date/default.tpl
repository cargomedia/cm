<select name="{$name}[year]" class="{$class}">
	<option value="">
		{translate 'Year'}
	</option>
	{for $i = $maxYear to $minYear step -1}
	<option value="{$i}" {if $i == $yy}selected="selected"{/if}>
		{$i}
	</option>
	{/for}
</select>
<select name="{$name}[month]" class="{$class}">
	<option value="">
		{translate 'Month'}
	</option>
	{for $i = 1 to 12}
	<option value="{$i}" {if $i == $mm}selected="selected"{/if}>
		{translate ".date.month.$i"}
	</option>
	{/for}
</select>
<select name="{$name}[day]" class="{$class}">
	<option value="">
		{translate 'Day'}
	</option>
	{for $i = 1 to 31}
	<option value="{$i}" {if $i == $dd}selected="selected"{/if}>
		{$i}
	</option>
	{/for}
</select>
