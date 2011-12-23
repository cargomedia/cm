<select name="{$name}[year]" class="{$class}">
	<option value="">
		{text phrase="%.forms._fields.date.year"}
	</option>
	{for $i = $maxYear to $minYear step -1}
	<option value="{$i}" {if $i == $yy}selected="selected"{/if}>
		{$i}
	</option>
	{/for}
</select>
<select name="{$name}[month]" class="{$class}">
	<option value="">
		{text phrase="%.forms._fields.date.month"}
	</option>
	{for $i = 1 to 12}
	<option value="{$i}" {if $i == $mm}selected="selected"{/if}>
		{text phrase="%.i18n.date.month_full_$i"}
	</option>
	{/for}
</select>
<select name="{$name}[day]" class="{$class}">
	<option value="">
		{text phrase="%.forms._fields.date.day"}
	</option>
	{for $i = 1 to 31}
	<option value="{$i}" {if $i == $dd}selected="selected"{/if}>
		{$i}
	</option>
	{/for}
</select>
