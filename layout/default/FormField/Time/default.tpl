{if $value}
  {$value = {date_time date=$value timeZone=$timeZone}}
{/if}
{tag el="input" name=$name id=$inputId type="text" value=$value class="textinput {$class}" placeholder=$placeholder}
