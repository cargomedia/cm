{extends file=$render->getLayoutPath('FormField/Geometry_Vector2/default.tpl', 'CM')}

{block name='item-after'}
  {tag el="input" name="{$name}[zCoordinate]" type="text" value=$zCoordinate class="textinput"}
{/block}
