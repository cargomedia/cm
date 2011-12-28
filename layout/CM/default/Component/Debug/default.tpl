<div class="panel">
	<ul class="alerts"></ul>
	<div class="buttons">
		<a href="javascript:;" class="actions">ACTIONS</a> ·
	{foreach from=$stats key=stat item=value}
		<a href="javascript:;" class="{$stat}">{$stat}</a> ({$value|@count})
	{/foreach}
	</div>
	<div class="containers">
	{foreach from=$stats key=key item=stat}
		<div class="{$key}">
			<ul>
				{foreach from=$stat item=entry}
					<li>{$entry}</li>
				{/foreach}
			</ul>
		</div>
	{/foreach}
		<div class="actions">
		{foreach from=$clearCacheButtons key=var item=text}
			<input id="{id tag=$var}" checked="checked" type="checkbox" name="{$var}" />
			<label for="{id tag=$var}">{$text}</label><br />
		{/foreach}
			<input class="clearCache" type="submit" value="Clear Cache">
		</div>
	</div>
</div>

{if $errors}
<ul class="errors">
	{foreach from=$errors item=error}
		<li>{$error.file}:{$error.line} <strong>{$error.msg}</strong></li>
	{/foreach}
</ul>
{/if}
