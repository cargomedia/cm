{extends file=$render->getLayoutPath('FormField/Suggest/default.tpl', 'CM')}

{block name='item-after'}
	{button_link class='detectLocation detect-location' theme="transparent" data=['click-spinner' => true] icon='crosshair' title={translate 'Get Current Location'}}
{/block}
