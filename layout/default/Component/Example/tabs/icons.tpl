<h3>Icons</h3>
{foreach $icons as $icon}
  <div class="iconBox">
    {icon icon=$icon}
    <span class="label">{$icon}</span>
  </div>
{/foreach}
