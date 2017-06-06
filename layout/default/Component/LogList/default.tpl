{button_link icon='trash' iconConfirm='trash-open' label='Flush' class='warning flushLog' data=['click-confirmed' => 'true']}

<ul class="aggregationPeriodList menu-pills">
  <li class="aggregationPeriod {if null === $aggregate}active{/if}">
    <a href="{linkUrl page=$urlPage level=$level type=$type}">No aggregation</a>
  </li>
  <li class="aggregationPeriod {if 0 === $aggregate}active{/if}">
    <a href="{linkUrl page=$urlPage level=$level type=$type aggregate=0}">Last Release</a>
  </li>
  {foreach $aggregationPeriodList as $aggregationPeriodItem}
    <li class="aggregationPeriod {if $aggregationPeriodItem === $aggregate}active{/if}">
      <a href="{linkUrl page=$urlPage level=$level type=$type aggregate=$aggregationPeriodItem}">{date_period period=$aggregationPeriodItem}</a>
    </li>
  {/foreach}
</ul>

<ul class="logList dataTable">
  <li class="log header">
    <div class="counter">
      {if isset($aggregationPeriod)}
        Count
      {else}
        Time
      {/if}
    </div>
    <div class="message">Message</div>
  </li>
  {foreach $logList as $log}
    <li class="log">
      {$recordTimestamp=$log.createdAt->toDateTime()->getTimestamp()}
      <div class="counter">
        {if $aggregationPeriod}
          {$log.count}
        {else}
          {date_timeago time=$recordTimestamp}
        {/if}
      </div>
      <div class="message">
        {if isset($log.level)}<span class="level level-{$log.level}">{$levelMap[$log.level]}</span>{/if} {$log.message|escape}
      </div>

      {if !empty($log.exception)}
        {$exception = $log.exception}
        <div class="exception">
          <div>{$exception.class}: {$exception.message|escape} in {$exception.file} on line {$exception.line}</div>
          {if (!empty($exception.trace))}
            {foreach from=$exception.trace item=traceRow name=traceLoop}
              <div>{$smarty.foreach.traceLoop.index}. {$traceRow.code|escape} at {$traceRow.file} line {$traceRow.line}</div>
            {/foreach}
          {elseif (!empty($exception.traceString))}
            <div>{$exception.traceString}</div>
          {/if}
          {if (!empty($exception.meta))}
            <div><span class="label">Exception meta info:</span>
              <pre>{print_r($exception.meta, true)|escape}</pre>
            </div>
          {/if}
        </div>
      {/if}

      {if !empty($log.context)}
        <div class="toggleNext">Context</div>
        <div class="toggleNext-content">
          <pre>{print_r($log.context, true)|escape}</pre>
        </div>
      {/if}

      {if !empty($log.loggerNotifications)}
        <div class="toggleNext">Logger notifications</div>
        <div class="toggleNext-content">
          <pre>{print_r($log.loggerNotifications, true)}</pre>
        </div>
      {/if}
    </li>
  {/foreach}

</ul>{paging paging=$logList urlPage=$urlPage urlParams=$urlParams}
