<div class="button button-{$buttonTheme} button-select">
  <input type="file" name="{$name}-file" multiple />
  <span class="icon icon-{block name="button-icon"}attach{/block}"></span>
  <span class="label">{if $text}{$text}{else}{block name="button-text"}{translate 'Read Files'}{/block}{/if}</span>
  <div class="spinner spinner-expanded"></div>
</div>
{button_link class='button-upload uploadFiles' theme='highlight' icon='upload' label={translate 'Upload Files'}}

<div class="dropInfo"><span class="icon icon-download"></span><span>{translate 'Drop files here.'}</span></div>

<ul class="previews"></ul>

<script type="text/template" class="tpl-preview">
  <li class="preview" data-type="[[=type]]">
    <div class="preview-inner">
      [[ if (isImage) { ]]
      <img src="[[=data]]" alt="[[=name]]">
      [[ } else { ]]
      <span class="icon icon-attach"></span>
      <div class="filename nowrap">[[=name]]</div>
      [[ } ]]
      [[ if (isImage) { ]]
      {link class='link-remove removeFile' label='Remove'}
      [[ } else { ]]
      {button_link class='button-remove removeFile' theme='transparent' icon='close' title='Remove'}
      [[ } ]]
      <div class="spinner spinner-expanded"></div>
    </div>
  </li>
</script>
