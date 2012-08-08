<ul class="tabs menu-tabs clearfix">
	{foreach $sections as $title => $filename}
		<li><a href="javascript:;">{$title}</a></li>
	{/foreach}
</ul>
<div class="tabs-content">
{foreach $sections as $title => $filename}
		<div>
			{include file="CM/default/Component/Example/{$filename}"}
		</div>
	{/foreach}
</div>
