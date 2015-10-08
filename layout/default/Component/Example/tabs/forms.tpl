{form name="SK_Form_Example" viewer=$viewer site=$render->getSite()}
{formField name='age' label="{translate 'age'}"}
{formField name='ageRange' label="{translate 'ageRange'}"}
{formField name='birthdate' label="{translate 'birthdate'}"}
{formField name='embedVideo' label="{translate 'embedVideo'}"}
{formField name='fullName' label="{translate 'fullName'}"}
{formField name='paymentProvider' label="{translate 'paymentProvider'}"}
{formField name='photoUpload' label="{translate 'photoUpload'}"}
{formField name='privacy' label="{translate 'privacy'}"}
{formField name='sex' label="{translate 'sex'}"}
{formField name='sexAndMatchSex' label="{translate 'sexAndMatchSex'}"}
{formField name='sexSet' label="{translate 'sexSet'}"}
{formField name='tags' label="{translate 'tags'}"}
{formField name='username' label="{translate 'username'}"}
{formField name='usernameSearch' label="{translate 'usernameSearch'}"}
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
