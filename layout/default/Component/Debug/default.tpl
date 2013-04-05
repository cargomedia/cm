<a class="debugIndication toggleDebugBar" href="javacript:;" title="{translate 'Debug (Click here or use [d] key)'}">D</a>

<div class="debugBar clearfix">
	<a href="javascript:;" class="panel" data-id="actions">{{translate 'Actions'}|strtoupper}</a>
	{foreach $stats as $key => $value}
		<a href="javascript:;" class="panel" data-id="{$key}">{$key}<span class="count"> ({$value|@count})</span></a>
	{/foreach}

	<div class="window actions">
		{foreach $clearCacheButtons as $key => $text}
			<input id="{id tag=$key}" checked="checked" type="checkbox" name="{$key}" />
			<label for="{id tag=$key}">{$text}</label>
			<br />
		{/foreach}
		{button_link class="clearCache" label="{translate 'Clear Cache'}"}
	</div>

	{foreach $stats as $key => $value}
		<div class="window {$key}">
			<ul>
				{foreach $value as $entry}
					<li>{$entry}</li>
				{/foreach}
			</ul>
		</div>
	{/foreach}
</div>
