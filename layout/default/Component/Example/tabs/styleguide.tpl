<div class="columns">
  <div class="column2">
    {box title={translate 'Logo'}}
     {img path="logo.png"}
    {/box}
    {box title={translate 'Headings'}}
      <h1>Heading 1</h1>
      <h2>Heading 2</h2>
      <h3>Heading 3</h3>
      <h4>Heading 4</h4>
      <h5>Heading 5</h5>
      <h6>Heading 6</h6>
      <p>This is a paragraph. The quick, brown fox jumps over a lazy dog. DJs flock by when MTV ax quiz prog. Junk MTV quiz graced by fox whelps. Bawds jog, flick quartz, vex nymphs. Waltz, bad nymph, for quick jigs vex!</p>
    {/box}
  </div>
  <div class="column2">
    {box title={translate 'Color Palette'}}
      <div class="color-palette colorTheme">@colorTheme: #e22b59</div>
      <div class="color-palette colorBgBody">@colorBgBody: #eeeeee</div>
      <div class="color-palette colorBg">@colorBg: #ffffff</div>
      <div class="color-palette colorHighlight">@colorHighlight: #f5cd24</div>
    {/box}
    {box class='text' title={translate 'Text'}}
      <p class="text-heading">Text Heading <span class="colorCode">@colorHeading: #e22b59</span></p>
      <p class="text-normal">Text Normal <span class="colorCode">@colorFg: #333333</span></p>
      <p class="text-subtle">Text Subtle <span class="colorCode">@colorFgSubtle: #737373</span></p>
      <p class="text-link">Text Link <span class="colorCode">@colorFgLink: #0a7bc3</span></p>
    {/box}
  </div>
</div>
