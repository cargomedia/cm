<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	</head>
	<body style="margin: 0px">

		<p>
			Dear {if $recipient}{$recipient->getDisplayName()}{else}user{/if},
		</p>

		<p>
			{$body}
		</p>

		<p>
			Thanks,<br />
				{$siteName}
		</p>
	</body>
</html>
