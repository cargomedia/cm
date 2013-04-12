<div class="captcha">
	<div class="captcha-image">
		<img src="{$render->getUrl("/captcha/{$render->getSite()->getId()}?id={$imageId}")}" />
		{link icon="reload" class="refreshCaptcha refresh-link" title="{translate 'Refresh'}"}
	</div>
	<div class="captcha-input">
		<label for="{$name}">{translate 'Enter Code'}</label>
		<input type="text" name="{$name}[value]" id="{$id}" class="textinput" maxlength="6" />
	</div>
	<input type="hidden" name="{$name}[id]" value="{$imageId}" />
</div>
