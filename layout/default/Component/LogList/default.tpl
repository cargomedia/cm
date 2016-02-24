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
      <div class="counter">
        {if $aggregationPeriod}
          {$log.count}
        {else}
          {date_timeago time=$log.createdAt->toDateTime()->getTimestamp()}
        {/if}
      </div>
      <div class="message">{if isset($log.level)}{$levelMap[$log.level]}{/if} - {$log.message|escape}</div>

      {if !empty($log.exception)}
        {$exception = $log.exception}
        <div class="label">Exception Info</div>
        <div><span class="label">Class:</span> {$exception.class}</div>
        <div><span class="label">Message:</span> {$exception.message}</div>
        <div><span class="label">Line:</span> {$exception.line}</div>
        <div><span class="label">File:</span> {$exception.file}</div>
        {if (!empty($exception.metaInfo))}
          <div><span class="label">MetaInfo:</span>
            <pre>{$exception.metaInfo|@print_r}</pre>
          </div>
        {/if}

        {if (!empty($exception.trace))}
          <div><span class="label">Trace:</span>
            {foreach from=$exception.trace item=traceRow name=traceLoop}
              <div>{$smarty.foreach.traceLoop.index}. {$traceRow.code} at {$traceRow.file} line {$traceRow.line}</div>
            {/foreach}
          </div>
        {elseif (!empty($exception.traceString))}
          <div><span class="label">Trace as string:</span> {$exception.traceString}</div>
        {/if}

      {/if}

      {if !empty($log.context)}
        {$context = $log.context}
        <div class="toggleNext">Meta Info</div>
        <div class="toggleNext-content">
          {if (!empty($context.extra))}
            <div><span class="label">Extra:</span>
              <pre>{$context.extra|@print_r}</pre>
            </div>
          {/if}
          {if (isset($context.user))}
            <div><span class="label">User: id-{$context.id}</span> <span class="label">name:</span> {$context.name}</div>
          {/if}
          {if (isset($context.httpRequest))}
            <div><span class="label">Request:</span> {$context.httpRequest.method} {$context.httpRequest.uri}</div>
            <div><span class="label">Query:</span>
              <pre>{$context.httpRequest.query|@print_r}</pre>
            </div>
            <div><span class="label">Server:</span>
              <pre>{$context.httpRequest.server|@print_r}</pre>
            </div>
            <div><span class="label">Headers:</span>
              <pre>{$context.httpRequest.headers|@print_r}</pre>
            </div>
            {if (isset($context.httpRequest.clientId))}
              <div><span class="label">ClientId:</span> {$context.httpRequest.clientId}</div>
            {/if}
          {/if}
          {if (isset($context.computerInfo))}
            <div>FQDN: {$context.computerInfo.fqdn} Version: {$context.computerInfo.phpVersion}</div>
          {/if}
        </div>
      {/if}
    </li>
  {/foreach}

</ul>{paging paging=$logList urlPage=$urlPage urlParams=$urlParams}
