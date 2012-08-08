<h2>Forms</h2>
<table>
	<tr>
		<td>
			{form name="CM_Form_Example" viewer=$viewer}
					{formField name='text' label="{translate 'Text'}"}
					{formField name='int' label="{translate 'Integer'}"}
					{formField name='location' label="{translate 'Location'}"}
					{formField name='locationSlider' label="{translate 'Radius'}"}
					{formField name='image' label="{translate 'Upload {$count} Photos' count='0-2'}"}
					{formField name='color' label="{translate 'Color'}"}
					{formField name='set' label="{translate 'Set'}"}
					{formField name='boolean' text="{translate 'Bool'}"}
					{formField name='setSelect1' label="{translate 'Set Select'}" display='radios'}
					{formField name='setSelect2' label="{translate 'Set Select'}"}
					{formAction action='go' label="{translate 'Go'}"}
				{/form}
		</td>
		<td>
			<h3>Markup</h3>
				{code language="html"}{literal}
					{form name="CM_Form_Example" viewer=$viewer}
						{formField name='text' label="{translate 'Text'}"}
						{formField name='int' label="{translate 'Integer'}"}
						{formField name='location' label="{translate 'Location'}"}
						{formField name='locationSlider' label="{translate 'Radius'}"}
						{formField name='image' label="{translate 'Upload {$count} Photos' count='0-2'}"}
						{formField name='color' label="{translate 'Color'}"}
						{formField name='set' label="{translate 'Set'}"}
						{formField name='boolean' text="{translate 'Bool'}"}
						{formField name='setSelect1' label="{translate 'Set Select'}" display='radios'}
						{formField name='setSelect2' label="{translate 'Set Select'}"}
						{formAction action='go' label="{translate 'Go'}"}
					{/form}
				{/literal}{/code}
			<br />
			<hr />

			<h4>Link</h4>
			{code language="html"}{literal}{formField name='[name]' label='[string]'}{/literal}{/code}
			<h4>Action</h4>
			{code language="html"}{literal}{formAction action='[action]' label='[string]'}{/literal}{/code}
		</td>
	</tr>
</table>