{strip}
  <ul class="previews">
    {foreach $value as $tmpFile}
      <li class="preview">
        {$field->getPreview($tmpFile, $render)}<input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
      </li>
    {/foreach}
  </ul>
{/strip}
<button class="button button-default button-upload uploadButton dropZone">
  <input type="file" name="{$name}-file" multiple />
  <span class="icon icon-{block name="button-icon"}upload{/block}"></span>
  <span class="label">{if $text}{$text}{else}{block name="button-text"}{translate 'Upload Files'}{/block}{/if}</span>
</button>
<div class="notSupported">*{translate 'Your browser does not support file uploads.'}</div>
