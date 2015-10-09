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
{formField name='setSelect3' label="Set Select with Prefix" labelPrefix="Sex"}
{formField name='treeselect' label="Tree select"}
  <div class="box">
    <div class="box-header">
      Below buttons' results show in developer console (please open it before)
    </div>
    <div class="box-body">
      {button_link class="showClientData" theme="highlight" label="Show client data"}
      {button_link class="showServerData" theme="highlight" label="Send and validate data"}
    </div>
  </div>
{/form}
