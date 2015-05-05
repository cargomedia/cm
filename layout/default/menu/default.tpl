{if !empty($menu_entries)}
  {strip}
    <ul class="{$menu_class}">
      {foreach from=$menu_entries item=entry}
        <li class="{$entry->getClass()} {if $entry->isActive($activePath, $activeParams)}active{/if} {if $entry->getIcon()}hasIcon{/if}" data-menu-entry-hash="{$entry->getHash()}">
          <a href="{linkUrl page=$entry->getPageName() params=$entry->getParams()}">
            {if null !== $entry->getIndication()}<span class="indication">{$entry->getIndication()}</span>{/if}
            {if null !== $entry->getIcon()}<span class="icon icon-{$entry->getIcon()}"></span>{/if}
            <span class="label">{translate $entry->getLabel()}</span>
          </a>
        </li>
      {/foreach}
    </ul>
  {/strip}
{/if}
