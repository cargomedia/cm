<section class="sheet">
	<header>
		<h1 class="logo">
			Your Company Logo </h1>
	</header>
	<article>
		foo={$foo|usertext}<br /> time={$now}<br />
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

	{form name="CM_Form_Example" viewer=$viewer}
		<table class="form">
			<tr>
				<td class="label">{label text="Integer" for="int"}:</td>
				<td class="value">{input name="int"}</td>
			</tr>
			<tr>
				<td class="label">{label text="Location" for="location"}:</td>
				<td class="value">{input name="location"} {input name="locationSlider"}</td>
			</tr>
			<tr>
				<td class="label">{label text="Image" for="image"}:</td>
				<td class="value">{input name="image" label="Upload 0-2 Photos"}</td>
			</tr>
			<tr>
				<td class="label">{label text="Color" for="color"}:</td>
				<td class="value">{input name="color"}</td>
			<tr>
				<td colspan="2" class="submit">
					{button action="go" label="Go"}
				</td>
			</tr>
		</table>
	{/form}
	</article>
	<footer>
		<h2>Technologies</h2>
		<div class="technologies">
			<a href="http://dev.w3.org/html5/spec/Overview.html">HTML5</a>
			<a href="http://www.w3.org/TR/CSS/">CSS3</a>
			<a href="http://www.php.net/">PHP 5.3</a>
			<a href="http://www.mysql.com/">MySQL</a>
			<a href="http://jquery.com/">jQuery</a>
			<a href="https://github.com/ruflin/Elastica">Elastica</a>
			<a href="http://jquerymobile.com/">jQuery Mobile</a>
			<a href="http://nginx.org/">nginx</a>
			<a href="http://nodejs.org/">nodeJS</a>
			<a href="http://www.linuxfoundation.org/">Linux</a>
			<a href="http://www.smarty.net/">Smarty</a>
			<a href="http://www.debian.org/">Debian 6</a>
			<a href="http://memcached.org/">Memcached</a>
			<a href="http://www.mongodb.org/">Mongo DB</a>
			<a href="https://www.varnish-cache.org/">Varnish Cache</a>
			<a href="http://lucene.apache.org/java/docs/index.html">lucene</a>
			<a href="http://redis.io/">redis</a>
		</div>
		<p class="copyright">&copy;<?php echo date('o'); ?> Your Company AG</p>
	</footer>
</section>