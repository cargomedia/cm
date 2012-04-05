<div>
	<input type="hidden" name="{$name}[]" />
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
				{$field->getPreview($tmpFile)}
				<input type="hidden" name="{$name}[]" value="{$tmpFile->getUniqid()}" />
			</li>
		{/foreach}
	</ul>
	<div class="file-uploader">
		<div class="qq-uploader">
			<div style="display:none;" class="qq-upload-drop-area"><span>{$textDropArea}</span></div>
			<div class="qq-upload-button">{$text}</div>
		</div>
	</div>
</div>
