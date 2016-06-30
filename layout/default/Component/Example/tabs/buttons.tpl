<h3>Basic</h3>
{button_link label="Button"}
{button_link label="Button with html <em>tags</em>" isHtmlLabel=true}
{button_link icon="like"}
{button_link icon="trophy" label="With Icon"}
{button_link label="As a link" href="/"}
{button_link icon="gift" label="Link with icon" href="/"}
<hr />
<h3>Large</h3>
{button_link class="button-large" label="Button"}
{button_link icon="gift" class="button-large" label="With Icon"}
{button_link class="button-large" label="As a link" href="/"}
{button_link icon="gift" class="button-large" label="Link with icon" href="/"}
<hr />
<h3>Themes</h3>
{button_link theme='default' label="Default"}
{button_link theme='highlight' label="Highlight"}
{button_link theme='success' label="Success"}
{button_link theme='danger' label="Danger"}
<hr />
<h3>Confirmation</h3>
{button_link class="confirmAction" label="With Confirmation" data=['click-confirmed' => true]}
{button_link class="confirmAction warning" icon='trash' iconConfirm='trash-open' label="With Warning" data=['click-confirmed' => true]}
