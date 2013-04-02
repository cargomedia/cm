{form name="CM_Form_Example" viewer=$viewer}
	{formField name='text' label="{translate 'Text'}"}
	{formField name='int' label="{translate 'Integer'}"}
	{formField name='location' label="{translate 'Location'}"}
	{formField name='locationSlider' label="{translate 'Radius'}"}
	{formField name='image' label="{translate 'Upload {$count} Photos' count='0-2'}"}
	{formField name='set' label="{translate 'Set'}"}
	{formField name='boolean' text="{translate 'Bool'}"}
	{formField name='setSelect1' label="{translate 'Set Select'}" display='radios'}
	{formField name='setSelect2' label="{translate 'Set Select'}"}
	{formAction action='go' label="{translate 'Go'}"}
{/form}
