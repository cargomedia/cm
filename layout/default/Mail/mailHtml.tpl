<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
      {less}

      /* Reset */

      body {
        width: 100% !important;
        min-width: 100%;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        margin: 0;
        padding: 0;
      }

      img {
        outline: none;
        text-decoration: none;
        -ms-interpolation-mode: bicubic;
        width: auto;
        max-width: 100%;
        float: left;
        clear: both;
        display: block;
      }

      a img {
        border: none;
      }

      p {
        margin: 0;
      }

      table {
        border-spacing: 0;
        border-collapse: collapse;
      }

      td {
        word-break: break-word;
        -webkit-hyphens: auto;
        -moz-hyphens: auto;
        hyphens: auto;
        border-collapse: collapse !important;
      }

      table, tr, td {
        padding: 0;
        vertical-align: top;
        text-align: left;
      }

      hr {
        color: @colorFgBorderEmphasize1;
        background-color: @colorFgBorderEmphasize1;
        height: 1px;
        border: none;
      }

      /* Typography */

      body, table, h1, h2, h3, h4, h5, h6, p, td {
        color: @colorFg;
        font-family: @fontFamily;
        font-size: @fontSize;
        -webkit-font-smoothing: antialiased;
        line-height: @fontLineHeight;
      }

      h1, h2, h3, h4, h5 {
        font-weight: bold;
        line-height: @fontLineHeightHeading;
      }

      h1, h2, h3 {
        font-family: @fontFamilyHeading;
        color: @fontColorHeading;
      }

      /* Base styles */

      a {
        color: @colorFgLink;
        text-decoration: none
      }

      a:hover {
        text-decoration: underline;
      }

      {/less}
    </style>
    {block name='head'}{/block}
  </head>
  <body>
    {block name='body'}
      <p>
        {if isset($recipient)}
          {translate 'Dear {$username}' username=$recipient->getDisplayName()|escape},
        {else}
          {translate 'Dear user'},
        {/if}
      </p>
      <p>
        {$body}
      </p>
      <p>
        {translate 'Thanks'},<br />
        {$siteName|escape}
      </p>
    {/block}
  </body>
</html>
