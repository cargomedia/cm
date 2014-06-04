{foreach $optionList as $itemValue => $itemLabel}
  {capture assign='itemHtml'}{strip}
    {block name='label'}{$itemLabel}{/block}
  {/strip}{/capture}
  {$optionList[$itemValue] = $itemHtml}
{/foreach}

{if $display === CM_FormField_Set_Select::DISPLAY_RADIOS}
  <ul id="{$inputId}" class="{$class}">
    {foreach $optionList as $itemValue => $itemLabel}
      <li class="set-item {$name}-value-{$itemValue}">
        <input id="{$inputId}-{$itemValue}" name="{$name}" type="radio" value="{$itemValue|escape}" {if $itemValue==$value}checked{/if} />
        <label for="{$inputId}-{$itemValue}" class="{$name}-label-{$itemValue}">
          {if $translate}
            {translate "{$translatePrefix}{$itemLabel}"|escape}
          {else}
            {$itemLabel|escape}
          {/if}
        </label>
      </li>
    {/foreach}
  </ul>
{/if}

{if $display === CM_FormField_Set_Select::DISPLAY_SELECT}
  {select id=$inputId name=$name class=$class optionList=$optionList translate=$translate translatePrefix=$translatePrefix selectedValue=$value placeholder=$placeholder labelPrefix=$labelPrefix}
{/if}
