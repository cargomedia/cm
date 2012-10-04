{function renderNode node=null}
	<li {if $node->hasNodes()}class="hasChildren"{/if}>
		<span class="toggle icon"></span>
		<div class="node" data-id="{$node->getId()|escape}" data-path="{$node->getPath()|escape}">
			{$node->getName()}
			<span class="count">({$node->getNodes()|count})</span>
		</div>
		<ul>
		{foreach $node->getNodes() as $child}
			{renderNode node=$child}
		{/foreach}
		</ul>
	</li>
{/function}

{tag el="input" name=$name id=$id type="hidden" value=$value}
{button_link icon="arrowDown" iconPosition='right' label={translate 'Select...'} class='selector'}
<ul class="options">
	<li class="unselect">None</li>
	{renderNode node=$tree->getRoot()}
</ul>