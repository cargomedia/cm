<h2>Opener Flyout</h2>
<table>
	<tr>
		<td>
			<div class="opener account">
				<a href="javascript:;" class="panel">
					Panel
				</a>
				<div class="window">
					<div class="flyout">
						<div class="flyout-arrow"></div>
						Window
					</div>
				</div>
			</div>
		</td>
		<td>
		{code language="html"}{literal}
			<div class="opener account">
				<a href="javascript:;" class="panel">
					Panel
				</a>
				<div class="window">
					<div class="flyout">
						<div class="flyout-arrow"></div>
						Window
					</div>
				</div>
			</div>
		{/literal}{/code}
		{code language="less"}{literal}
			.opener {
				position: relative;

				.window {
					position: absolute;
					display: none;
					top: 100%;
					margin-top: 10px;

					.flyout {
					position: relative;
					padding: 10px;
					}
				}
			}
		{/literal}{/code}
		</td>
	</tr>
</table>
