foo={$foo|usertext}<br />
time={$now}<br />
viewer=<span class="user">{if $viewer}{$viewer->getDisplayName()}{else}null{/if}</span><br />
<hr />

{button_link class="reload" label="reload()" icon="reload"}
{button_link class="popout" label="popOut()"}
{button_link class="popin" label="popIn()"}
{button_link class="load" label="load()"}
{button_link class="load_callback" label="load()+callback"}
{button_link class="remove" label="remove()"}
<hr />

{button_link class="rpc" label="rpc: time()"}
{button_link class="call" label="ajax: test()"}
<hr />

{button_link class="error_500_text_callback" label="err: 500+text+callback"}
{button_link class="error_599_text" label="err: 599+text"}
{button_link class="error_CM_Exception_public_callback" label="err: CM_Exception+public+callback"}
{button_link class="error_CM_Exception_public" label="err: CM_Exception+public"}
{button_link class="error_CM_Exception" label="err: CM_Exception"}
{button_link class="error_CM_Exception_AuthRequired_public_callback" label="err: CM_Exception_AuthRequired+public+callback"}
{button_link class="error_CM_Exception_AuthRequired_public" label="err: CM_Exception_AuthRequired+public"}
{button_link class="error_CM_Exception_AuthRequired" label="err: CM_Exception_AuthRequired"}
<hr />

{form name="CM_Form_Example" viewer=$viewer}
	<div class="formField">
		{label text="Text" for="text"}
		{input name="text"}
	</div>
	<div class="formField">
		{label text="Integer" for="int"}
		{input name="int"}
	</div>
	<div class="formField">
		{label text="Location" for="location"}
		{input name="location"} {input name="locationSlider"}
	</div>
	<div class="formField">
		{label text="Image" for="image"}
		{input name="image" label="Upload 0-2 Photos"}
	</div>
	<div class="formField">
		{label text="Color" for="color"}
		{input name="color"}
	</div>
	<div class="formField submit">
		{button action="go" label="Go"}
	</div>
{/form}

<div class="stream">
	<div class="output"></div>
	{button_link class="ping" label="ajax: ping()"}
</div>
