{function renderNode node=null}
	<li {if $node->hasNodes()}class="hasChildren"{/if}>
		<span class="icon icon-arrow-right toggleSubtree"></span>
		<div class="node selectNode" data-id="{$node->getId()|escape}" data-path="{$node->getPath()|escape}">
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
{button_link icon="arrow-down" iconPosition='right' label={translate 'Select...'} class='selector toggleWindow'}
<ul class="options">
	<li class="unselect unselectNode">{translate 'None'}</li>
	{renderNode node=$tree->getRoot()}
</ul>
