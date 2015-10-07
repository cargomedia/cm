{form name="CM_Form_Example" viewer=$viewer}
{formField name='text' label="{translate 'Text'}" placeholder="{translate 'Placeholder'}"}
{formField name='email' label="{translate 'Email'}" placeholder="{translate 'Email'}"}
{formField name='password' label="{translate 'Password'}" placeholder="{translate 'Password'}"}
{formField name='textarea' label="{translate 'Textarea'}" placeholder="{translate 'Placeholder'}"}
{formField name='float' label="{translate 'Float'}" placeholder="{translate 'Float'}"}
{formField name='money' label="{translate 'Money'}" placeholder="{translate 'Money'}"}
{formField name='url' label="{translate 'Url'}" placeholder="{translate 'Url'}"}
{formField name='int' label="{translate 'Integer'}"}
{formField name='location' label="{translate 'Location'}"}
{formField name='locationSlider' label="{translate 'Radius'}"}
{formField name='file' label="{translate 'Upload Files'}"}
{formField name='image' label="{translate 'Upload {$count} Photos' count='0-2'}"}
{formField name='color' label="{translate 'Color'}"}
{formField name='date' label="{translate 'Date'}"}
{formField name='birthdate' label="{translate 'Birth Date between 18 and 30'}"}
{formField name='geopoint' label="{translate 'Geo Point'}"}
{formField name='set' label="{translate 'Set'}"}
{formField name='boolean' text="{translate 'Bool'}"}
{formField name='booleanSwitch' text="{translate 'Bool Switch'}" display='switch'}
{formField name='setSelect1' label="{translate 'Set Select'}" display='radios'}
{formField name='setSelect2' label="{translate 'Set Select'}"}
{formField name='setSelect3' label="{translate 'Set Select with Prefix'}" labelPrefix="{translate 'Sex'}"}
{formField name='treeselect' label="{translate 'Tree select'}"}
{formAction action='Go' label="{translate 'Go'}"}
{/form}
