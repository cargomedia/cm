<div class="example-info">
  Changes to form fields are validated and displayed in the developer console of the browser!
</div>

{form name="CM_Form_Example" viewer=$viewer}
{formField name='text' label="Text" placeholder="Placeholder"}
{formField name='email' label="Email" placeholder="Email"}
{formField name='password' label="Password" placeholder="Password"}
{formField name='textarea' label="Textarea" placeholder="Placeholder"}
{formField name='float' label="Float" placeholder="Float"}
{formField name='money' label="Money" placeholder="Money"}
{formField name='url' label="Url" placeholder="Url"}
{formField name='int' label="Integer"}
{formField name='location' label="Location"}
{formField name='locationSlider' label="Radius"}
{formField name='file' label="Upload Files"}
{formField name='image' label="Upload Photos"}
{formField name='color' label="Color"}
{formField name='date' label="Date"}
{formField name='birthdate' label="Birth Date between 18 and 30"}
{formField name='geopoint' label="Geo Point"}
{formField name='set' label="Set"}
{formField name='boolean' text="Bool"}
{formField name='booleanSwitch' text="Bool Switch" display='switch'}
{formField name='setSelect1' label="Set Select" display='radios'}
{formField name='setSelect2' label="Set Select"}
{formField name='setSelect3' label="Set Select with Prefix" labelPrefix="FooBar"}
{formField name='treeselect' label="Tree select"}
{/form}
