<div class="debugBar clearfix">
  <a href="javascript:;" class="panel actions toggleWindow" data-name="actions">{{translate 'Actions'}}</a>
  {foreach $stats as $name => $value}
    <a href="javascript:;" class="panel toggleWindow" data-name="{$name|escape}">{$name|escape}<span class="count"> ({$value|@count})</span></a>
  {/foreach}

  <div class="window actions">
    {foreach $cacheNames as $name}
      <p>{checkbox class=$name label=$name checked=true}</p>
    {/foreach}
    {button_link class="clearCache" label="{translate 'Clear Cache'}" title="{translate 'Click here or use [c] key'}"}
  </div>

  {foreach $stats as $name => $value}
    <div class="window {$name|escape}">
      <ul>
        {foreach $value as $entry}
          <li>
            {if is_array($entry)}
              <ul class="entryList">
                {foreach $entry as $item}
                  <li>{$item|escape}</li>
                {/foreach}
              </ul>
            {else}
              {$entry|escape}
            {/if}
          </li>
        {/foreach}
      </ul>
    </div>
  {/foreach}
  {link icon="bug" class="debugIndication toggleDebugBar" title="{translate 'Debug (Click here or use [d] key)'}"}
</div>
