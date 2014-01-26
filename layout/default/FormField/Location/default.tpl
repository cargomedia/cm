{extends file=$render->getLayoutPath('FormField/Suggest/default.tpl', 'CM')}

{block name='item-before'}
	{button_link class='detectLocation current-location' icon='crosshair' title={translate 'Get Current Location'}}
{/block}
