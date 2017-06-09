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
{formField name='int' label="Integer" placeholder="Integer"}
{formField name='slider' label="Slider"}
{formField name='sliderRange' label="Slider Range"}
{formField name='location' label="Location"}
{formField name='locationSlider' label="Radius"}
{formField name='file' label="Upload Files"}
{formField name='image' label="Upload Photos"}
{formField name='color' label="Color"}
{formField name='color2' label="Color (Palette)"}
{formField name='date' label="Date"}
{formField name='dateTimeInterval' label = "DateTimeInterval" placeholderStart='Starting Time' placeholderEnd='End'}
{formField name='birthdate' label="Birth Date between 18 and 30"}
{formField name='geopoint' label="Geo Point"}
{formField name='set' label="Set"}
{formField name='boolean' text="Bool"}
{formField name='booleanSwitch' text="Bool Switch" display='switch'}
{formField name='booleanButton' text="Bool Button" display='button'}
{formField name='booleanButton2' text="Bool Button Highlight" display='button' buttonTheme='highlight' buttonIcon='trophy'}
{formField name='setSelect1' label="Set Select" display='radios'}
{formField name='setSelect2' label="Set Select" placeholder=true}
{formField name='setSelect3' label="Set Select with Prefix" labelPrefix="FooBar" placeholder=true}
{formField name='treeselect' label="Tree select"}
{formField name='vector2' label="Vector 2D"}
{formField name='vector3' label="Vector 3D"}
{formField name='captcha' label="Captcha"}
{formAction action='Submit' label='Submit'}
{/form}

<hr />
<h3>Autosave</h3>
{form name="CM_Form_ExampleAutosave" viewer=$viewer autosave="Submit"}
{formField name='text' label="Text" placeholder="Placeholder"}
{formField name='booleanSwitch' text="Bool Switch" display='switch'}
{formField name='setSelect1' label="Set Select" display='radios'}
{formField name='setSelect2' label="Set Select" placeholder=true}
{formField name='setSelect3' label="Set Select with Prefix" labelPrefix="FooBar" placeholder=true}
{formField name='treeselect' label="Tree select"}
{/form}
