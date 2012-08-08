<h2>{translate 'Menus'}</h2>
<table class="menus">
	<tr>
		<td>
			{$menu = [['label' => 'Entry 1', 'icon' => 'close'], ['label' => 'Entry 2', 'icon' => 'close'], ['label' => 'Entry 3', 'icon' => 'close']]}
			<h3>Menu Sub / Dropdown</h3>
			{if !empty($menu)}
				<ul class="menu-dropdown">
					{foreach $menu as $menuEntry}
						<li>
							<a href="javascript:;">
								{if !empty($menuEntry['icon'])}<span class="icon {$menuEntry['icon']}"></span>{/if}
								<span class="label">{translate $menuEntry['label']}</span>
							</a>
						</li>
					{/foreach}
				</ul>
				<h3>Menu Tabs</h3>
				<ul class="menu-tabs clearfix">
					{foreach $menu as $menuEntry}
						<li>
							<a href="javascript:;">
								{if !empty($menuEntry['icon'])}<span class="icon {$menuEntry['icon']}"></span>{/if}
								<span class="label">{translate $menuEntry['label']}</span>
							</a>
						</li>
					{/foreach}
				</ul>
				<h3>Menu Pills</h3>
				<ul class="nav nav-pills">
					{foreach $menu as $menuEntry}
						<li>
							<a href="javascript:;">
								{if !empty($menuEntry['icon'])}<span class="icon {$menuEntry['icon']}"></span>{/if}
								<span class="label">{translate $menuEntry['label']}</span>
							</a>
						</li>
					{/foreach}
				</ul>
			{/if}
		</td>
		<td>
			<h3>Markup</h3>
			{code language='html'}
<ul class="menu-dropdown">
{foreach $menu as $menuEntry}
	<li>
		<a href="javascript:;">
			{if !empty($menuEntry['icon'])}<span class="icon {$menuEntry['icon']}"></span>{/if}
			<span class="label">{translate $menuEntry['label']}</span>
		</a>
	</li>
{/foreach}
</ul>
			{/code}
			<h3>Smarty Helper</h3>
			{code language="html"}{literal}{menu name="[optional: browse, user, account, about ]" class="[optional: menu-sub, menu-pills, menu-tabs]" template='[optional: tree]' depth=[optional: int]}{/literal}{/code}
		</td>
	</tr>
</table>