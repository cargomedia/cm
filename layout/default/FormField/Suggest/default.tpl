{$prePopulate = []}
{if $value}
  {foreach $value as $valueItem}
    {$prePopulate[] = $field->getSuggestion($valueItem, $render)}
  {/foreach}
{/if}
<div class="input-wrapper">
  {tag el="input" name=$name type="text" class="textinput {$class}" data-pre-populate="{$prePopulate|@json_encode}" data-placeholder=$placeholder}
  {block name='item-after'}{/block}
</div>
