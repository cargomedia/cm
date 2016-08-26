<h2>{translate 'This is an Example'}</h2>
{if $viewer}{translate 'Hello {$user}!' user=$viewer->getDisplayName()|escape}{/if}
<br />  foo={usertext text=$foo mode='simple'}<br />  time={$now}<br />
<hr />

{button_link class="reloadComponent" label="reload()" icon="refresh"}
{button_link class="popoutComponent" label="popOut()"}
{button_link class="popinComponent" label="popIn()"}
{button_link class="multiLevelPopoutComponent" label="Multi-level PopOut()"}
{button_link class="loadComponent" label="load()"}
{button_link class="loadComponent_callback" label="load()+callback"}
{button_link class="removeComponent" label="remove()"}
<hr />

{button_link class="callRpcTime" label="rpc: time()" data=['click-spinner' => true]}
{button_link class="callAjaxTest" label="ajax: test()" data=['click-spinner' => true]}
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

<div class="innerPopup first">
  <h3>Popup Level 1</h3>
  {button_link class="innerPopinComponent" label="PopIn()"}
  {button_link class="innerPopoutComponent" label="PopOut()"}

  <div class="innerPopup">
    <h3>Popup Level 2</h3>
    {button_link class="innerPopinComponent" label="PopIn()"}
    {button_link class="innerPopoutComponent" label="PopOut()"}

    <div class="innerPopup">
      <h3>Popup Level 3</h3>
      {button_link class="innerPopinComponent" label="PopIn()"}
      {button_link class="innerPopoutComponent" label="PopOut()"}
    </div>
  </div>
</div>
