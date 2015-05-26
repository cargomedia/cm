{strip}
  <a href="{$href|escape}" style="{less}
    display: inline-block;
    background-color: @colorBgButtonHighlight;
    color: @colorFgButtonHighlight;
    border-style: solid;
    border-color: @colorBgButtonHighlight;
    border-width: @sizeButton/4 @sizeButton/2;
    border-radius: @borderRadiusInput;
    text-decoration: none !important;
  {/less}">{$label}</a>
{/strip}
