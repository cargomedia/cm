{strip}
  <ul class="previews">
    {foreach $value as $tmpFile}
      <li class="preview">
        {$field->getPreview($tmpFile, $render)}<input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
      </li>
    {/foreach}
  </ul>
{/strip}
<div class="button button-{$buttonTheme} button-upload uploadButton dropZone">
  <input type="file" name="{$name}-file" multiple />
  {block name="button-icon"}{icon icon='upload'}{/block}
  <span class="label">{if $text}{$text}{else}{block name="button-text"}{translate 'Upload Files'}{/block}{/if}</span>
  <div class="spinner spinner-expanded"></div>
</div>

<div class="notSupported">*{translate 'Your browser does not support file uploads.'}</div>
