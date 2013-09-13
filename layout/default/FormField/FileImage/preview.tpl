{extends file=$render->getLayoutPath('FormField/File/preview.tpl', 'CM')}

{block name="file"}<img src="{$render->getUrlUserContent($file)}" />{/block}
