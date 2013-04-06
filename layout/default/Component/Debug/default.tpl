<div class="debugBar clearfix">
	<a href="javascript:;" class="panel actions" data-name="actions">{{translate 'Actions'}}</a>
	{foreach $stats as $name => $value}
		<a href="javascript:;" class="panel" data-name="{$name}">{$name}<span class="count"> ({$value|@count})</span></a>
	{/foreach}

	<div class="window actions">
		{foreach $clearCacheButtons as $name => $text}
			<p>
				<input id="{$name}" checked="checked" type="checkbox" name="{$name}" />
				<label for="{$name}">{$text}</label>
			</p>
		{/foreach}
		{button_link class="clearCache" label="{translate 'Clear Cache'}"}
	</div>

	{foreach $stats as $name => $value}
		<div class="window {$name}">
			<ul>
				{foreach $value as $entry}
					<li>{$entry}</li>
				{/foreach}
			</ul>
		</div>
	{/foreach}
	<a class="debugIndication toggleDebugBar" href="javacript:;" title="{translate 'Debug (Click here or use [d] key)'}"><span class="icon-debug"></span></a>
</div>
