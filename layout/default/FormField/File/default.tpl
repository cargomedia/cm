<ul class="previews">
  {foreach $value as $tmpFile}
    <li class="preview">
      {$field->getPreview($tmpFile, $render)}
      <input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
    </li>
  {/foreach}
</ul>
<div class="upload">
  {if !$skipDropZone}
    <input type="file" name="{$name}-file" multiple />
    <div class="dropInfo">
      <p class="dropInfo-icon {block name="icon"}icon-upload{/block}"></p>
      <p class="dropInfo-message">{block name="message"}{translate 'Click or drag files here'}{/block}</p>
    </div>
  {else}
    <button class="button button-default uploadButton">
      <input type="file" name="{$name}-file" multiple />{if $text}{$text}{else}{block name="button-text"}{translate 'Upload Files'}{/block}{/if}
    </button>
  {/if}
</div>
<div class="notSupported">*{translate 'Your browser does not support file uploads.'}</div>
