<h2>{translate 'This is an Example'}</h2>
	{if $viewer}{translate 'Hello {$user}!' user=$viewer->getDisplayName()|escape}{/if}<br />	foo={usertext text=$foo mode='simple'}<br />	time={$now}<br />
<hr />

	{button_link class="reloadComponent" label="reload()" icon="reload"}
	{button_link class="popoutComponent" label="popOut()"}
	{button_link class="popinComponent" label="popIn()"}
	{button_link class="loadComponent" label="load()"}
	{button_link class="loadComponent_callback" label="load()+callback"}
	{button_link class="removeComponent" label="remove()"}
<hr />

	{button_link class="callRpcTime" label="rpc: time()"}
	{button_link class="callAjaxTest" label="ajax: test()"}
<hr />

	{button_link class="throwError_500_text_callback" label="err: 500+text+callback"}
	{button_link class="throwError_599_text" label="err: 599+text"}
	{button_link class="throwError_CM_Exception_public_callback" label="err: CM_Exception+public+callback"}
	{button_link class="throwError_CM_Exception_public" label="err: CM_Exception+public"}
	{button_link class="throwError_CM_Exception" label="err: CM_Exception"}
	{button_link class="throwError_CM_Exception_AuthRequired_public_callback" label="err: CM_Exception_AuthRequired+public+callback"}
	{button_link class="throwError_CM_Exception_AuthRequired_public" label="err: CM_Exception_AuthRequired+public"}
	{button_link class="throwError_CM_Exception_AuthRequired" label="err: CM_Exception_AuthRequired"}
<hr />
<div class="stream">
	<div class="output"></div>
	{button_link class="callAjaxPing" label="ajax: ping()"}
</div>
