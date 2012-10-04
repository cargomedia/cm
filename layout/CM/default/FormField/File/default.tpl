<div>
	<ul style="display:none;" class="previewsTemplate">
		<li>
			<span class="qq-upload-spinner"></span>
			<span style="display:none;" class="qq-upload-file"></span>
			<span style="display:none;" class="qq-upload-size"></span>
			<span style="display:none;" class="qq-upload-cancel"></span>
		</li>
	</ul>
	<ul class="previews">
		{foreach $value as $tmpFile}
			<li class="qq-upload-success">
				{$field->getPreview($tmpFile, $render)}
				<input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
			</li>
		{/foreach}
	</ul>
	<div class="file-uploader">
		<div class="qq-uploader">
			<div style="display:none;" class="qq-upload-drop-area"><span>{translate 'Drop files here to upload.'}</span></div>
			<div class="qq-upload-button">{if isset($text)}{$text}{else}{translate 'Upload File'}{/if}</div>
		</div>
	</div>
</div>
