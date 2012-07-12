{if $recipient}
	{translate 'Dear {$username}' username=$recipient->getDisplayName()},
{else}
	{translate 'Dear user'},
{/if}


{$body}

{translate 'Thanks'},
 {$siteName}
