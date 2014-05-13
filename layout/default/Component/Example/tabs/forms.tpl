{form name="CM_Form_Example" viewer=$viewer}
{formField name='text' label="{translate 'Text'}"}
{formField name='int' label="{translate 'Integer'}"}
{formField name='location' label="{translate 'Location'}"}
{formField name='locationSlider' label="{translate 'Radius'}"}
{formField name='file' label="{translate 'Upload Files'}"}
{formField name='image' label="{translate 'Upload {$count} Photos' count='0-2'}"}
{formField name='color' label="{translate 'Color'}"}
{formField name='date' label="{translate 'Date'}"}
{formField name='set' label="{translate 'Set'}"}
{formField name='boolean' text="{translate 'Bool'}"}
{formField name='booleanSwitch'  text="{translate 'Bool Switch'}" template='switch'}
{formField name='setSelect1' label="{translate 'Set Select'}" display='radios'}
{formField name='setSelect2' label="{translate 'Set Select'}"}
{formField name='setSelect3' label="{translate 'Set Select with Prefix'}" labelPrefix="{translate 'Sex'}"}
{formAction action='Go' label="{translate 'Go'}"}
{/form}
