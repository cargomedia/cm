{foreach $siteSettingsList as $siteSettings}
  <div class="toggleNext">{$siteSettings->findClassName()}({$siteSettings->getId()})</div>
  <div class="toggleNext-content">
    {component name="CM_Component_SiteSettings" siteSettings=$siteSettings}
  </div>
{/foreach}
