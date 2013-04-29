<ul class="previews">
	{foreach $value as $tmpFile}
		<li class="preview">
			{$field->getPreview($tmpFile, $render)}
			<input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
		</li>
	{/foreach}
</ul>
<div style="display:none;" class="dropZone"><span>{translate 'Drop files here to upload.'}</span></div>
<div class="uploadButton">
	<input type="file" name="{$name}-file" multiple />
	{if isset($text)}{$text}{else}{translate 'Upload File'}{/if}
</div>
