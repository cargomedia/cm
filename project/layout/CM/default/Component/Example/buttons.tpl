<h2>{translate 'Buttons'}</h2>
<table>
	<tr>
		<td>
			{button_link icon="close"}
			{button_link label="Button"}
			{button_link icon="close" label="With Icon"}
			<br /><br />
			{button_link class="alt" label="Button"}
			{button_link icon="close" class="alt" label="With Icon"}
			<br /><br />
			{button_link class="large" icon="close"}
			{button_link class="large" label="Button"}
			{button_link class="large" icon="close" label="With Icon"}
			<br /><br />
			{button_link class="large alt" label="Button"}
			{button_link class="large alt" icon="close" label="With Icon"}
			<br /><br />
			{button_link label="Tooltip" title="Tooltip"}
		</td>
		<td>
			<h3>Markup</h3>
					{code language="html"}{literal}
					{button_link icon="close"}
					{button_link label="Button"}
					{button_link icon="close" label="With Icon"}
					{button_link class="alt" label="Button"}
					{button_link icon="close" class="alt" label="With Icon"}
					{button_link class="large" icon="close"}
					{button_link class="large" label="Button"}
					{button_link class="large" icon="close" label="With Icon"}
					{button_link class="large alt" label="Button"}
					{button_link class="large alt" icon="close" label="With Icon"}
				{/literal}{/code}
			<br />
			<hr />
			<h4>Link</h4>
			{code language="html"}{literal}{button_link path="[optional]" icon="[optional]" class="[optional: alt, large, string]" label="[optional: string]"}{/literal}{/code}
			<h4>Form</h4>
			{code language="html"}{literal}{button path="[optional]" icon="[optional]" class="[optional: alt, large, string]" label="[optional: string]"}{/literal}{/code}
			<h4>With Tooltip</h4>
			{code language="html"}{literal}{button_link path="[optional]" icon="[optional]" class="[optional: alt, large, string]" label="[optional: string]" title="[string]"}{/literal}{/code}
		</td>
	</tr>
</table>