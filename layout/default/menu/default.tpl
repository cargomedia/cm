{if !empty($menu_entries)}
  {strip}
    <ul class="{$menu_class}">
      {foreach from=$menu_entries item=entry}
        <li class="navButton {$entry->getClass()} {if $entry->isActive($activePath, $activeParams)}active{/if}" data-menu-entry-hash="{$entry->getHash()}">
          <a href="{linkUrl page=$entry->getPageName() params=$entry->getParams()}">
            {if null !== $entry->getIcon()}
              <span class="icon icon-{$entry->getIcon()}">
                {if null !== $entry->getIndication()}<span class="indication indication-{$entry->getIndication()}"></span>{/if}
              </span>
            {/if}
          </a>
        </li>
      {/foreach}
    </ul>
  {/strip}
{/if}
