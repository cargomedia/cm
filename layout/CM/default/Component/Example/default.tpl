foo={$foo|usertext}<br />	time={$now}<br />
<p class='username'>Username: {if $viewer}{$viewer->getDisplayName()}{else}Not Logged in!{/if}</p>
<hr />

{button_link class="reload" label="reload()" icon="reload"}
{button_link class="popout" label="popOut()"}
{button_link class="popin" label="popIn()"}
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