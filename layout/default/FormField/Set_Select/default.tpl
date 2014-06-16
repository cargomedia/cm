{foreach $optionList as $itemValue => $itemLabel}
  {capture assign='itemHtml'}{strip}
    {block name='label'}{$itemLabel}{/block}
  {/strip}{/capture}
  {$optionList[$itemValue] = $itemHtml}
{/foreach}

{viewTemplate file=$display}
