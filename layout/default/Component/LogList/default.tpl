{button_link icon='delete' iconConfirm='delete-confirm' label='Flush' class='warning flushLog' data=['click-confirmed' => 'true']}

<ul class="aggregationPeriodList menu-pills">
  <li class="aggregationPeriod {if null === $aggregate}active{/if}">
    <a href="{linkUrl page=$urlPage type=$type}">No aggregation</a>
  </li>
  <li class="aggregationPeriod {if 0 === $aggregate}active{/if}">
    <a href="{linkUrl page=$urlPage type=$type aggregate=0}">Last Release</a>
  </li>
  {foreach $aggregationPeriodList as $aggregationPeriodItem}
    <li class="aggregationPeriod {if $aggregationPeriodItem === $aggregate}active{/if}">
      <a href="{linkUrl page=$urlPage type=$type aggregate=$aggregationPeriodItem}">{date_period period=$aggregationPeriodItem}</a>
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
          {date_timeago time=$log.timeStamp}
        {/if}
      </div>
      <div class="message">{$log.msg|escape}</div>
      {if !empty($log.metaInfo)}
        <div class="toggleNext">Meta Info</div>
        <div class="toggleNext-content">
          {foreach $log.metaInfo as $key => $value}
            <div class="tableField clearfix">
              <div class="label">{$key|escape}</div>
              <div class="value">{$value|@varline|escape}</div>
            </div>
          {/foreach}
        </div>
      {/if}
    </li>
  {/foreach}

</ul>{paging paging=$logList urlPage=$urlPage urlParams=$urlParams}
