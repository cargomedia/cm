<div class="debugBar clearfix">
	<a href="javascript:;" class="panel actions toggleWindow" data-name="actions">{{translate 'Actions'}}</a>
	{foreach $stats as $name => $value}
		<a href="javascript:;" class="panel toggleWindow" data-name="{$name}">{$name}<span class="count"> ({$value|@count})</span></a>
	{/foreach}

	<div class="window actions">
		{foreach $cacheNames as $name}
			<p>
				<input class="{$name}" checked="checked" type="checkbox" name="{$name}" />
				<label for="{$name}">{$name}</label>
			</p>
		{/foreach}
		{button_link class="clearCache" label="{translate 'Clear Cache'}"}
	</div>

	{foreach $stats as $name => $value}
		<div class="window {$name}">
			<ul>
				{foreach $value as $entry}
					<li>
						{if is_array($entry)}
							<ul class="entryList">
								{foreach $entry as $item}
									<li>{$item}</li>
								{/foreach}
							</ul>
						{else}
							{$entry}
						{/if}
					</li>
				{/foreach}
			</ul>
		</div>
	{/foreach}
	{link icon="debug" class="debugIndication toggleDebugBar" title="{translate 'Debug (Click here or use [d] key)'}"}
</div>
