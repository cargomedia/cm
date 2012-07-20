{if $recipient}
{translate 'Dear {$username}' username=$recipient->getDisplayName()|escape},
{else}
{translate 'Dear user'},
{/if}

{$body}

{translate 'Thanks'},
 {$siteName}
