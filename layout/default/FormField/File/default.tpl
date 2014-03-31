<ul class="previews">
  {foreach $value as $tmpFile}
    <li class="preview">
      {$field->getPreview($tmpFile, $render)}
      <input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
    </li>
  {/foreach}
</ul>
{if !$skipDropZone}
  <div class="dropInfo">
    <p class="dropInfo-icon {block name="icon"}icon-upload{/block}"></p>
    <p class="dropInfo-message">{block name="message"}{translate 'Drag files here'}{/block}</p>
    <p class="dropInfo-divide">- {translate 'or'} -</p>
  </div>
{/if}
<div class="uploadButton">
  <input type="file" name="{$name}-file" multiple />
  {if isset($text)}{$text}{else}{block name="button-text"}{translate 'Upload Files'}{/block}{/if}
</div>
<div class="notSupported">*{translate 'Your browser does not support file uploads.'}</div>
