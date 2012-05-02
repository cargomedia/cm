<div class="captcha_container">
	<div class="captcha_image">
		<img src="{$render->getSite()->getUrl()}captcha/{$render->getSite()->getId()}/?id={$imageId}" />
		<a class="icon reload refresh" title="Refresh" href="javascript:;"></a>
	</div>
	<div class="captcha_input">
		<label for="{$name}">Number</label>
		<input type="text" name="{$name}[value]" id="{$id}" class="textinput" maxlength="6" />
	</div>
	<input type="hidden" name="{$name}[id]" value="{$imageId}" />
</div>
