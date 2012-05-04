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
			<div style="display:none;" class="qq-upload-drop-area"><span>{$textDropArea}</span></div>
			<div class="qq-upload-button">{$text}</div>
		</div>
	</div>

	<div class="aurigma">
		<a href="http://itunes.apple.com/en/app/aurigma-up/id432611633?mt=8">Install App</a>
		<a href="aurup:?uploadUrl=http%3A%2F%2Fwww.fuckbook.dev%2Fupload&redirectUrl=http%3A%2F%2Fwww.fuckbook.dev%2Fphotos%3Fuser%3D26406&licenseKey=79FF1-0008F-C3710-00008-9646B-EF564B&cookies=sessionId%3Dfoo">Upload Images</a>
	</div>
</div>
