<div class="article">
	<ul>
		<li>
			HTML/CSS: <a href="http://google-styleguide.googlecode.com/svn/trunk/htmlcssguide.xml">Use Google Code Style Guide</a>
		</li>
		<li>
			Use CamelCase
		</li>
		<li>
			Use "-" for related child selectors (e.g. "clipSlide" & "clipSlide-handle")
		</li>
		<li>
			Use exclusive CSS class for JS bindings
		</li>
	</ul>
</div>
<h3>Example (toggleNext jQuery Plugin)</h3>
{code language="html5"}
<div class="toggleNext">Some Link</div>
<div class="toggleNext-content">
	Some Content
</div>
{/code}
{code language="css"}
.toggleNext {
	cursor: pointer;
}

.toggleNext-content {
	display: none;
}
{/code}
{code language="js"}
(function($) {
	$.fn.toggleNext = function() {
		return this.each(function() {
			var $toggler = $(this);
			var content = $toggler.next('.toggleNext-content');

			if (!content.length || $toggler.data('toggleNext')) {
				return;
			}

			var icon = $('<span />').addClass('icon toggle');
			$toggler.prepend(icon);

			if ($toggler.hasClass('active')) {
				icon.addClass('active');
				content.show();
			}

			$toggler.on('click.toggleNext', function() {
				$toggler.toggleClass('active');
				icon.toggleClass('active');
				content.slideToggle(100);
			});
			$toggler.data('toggleNext', true);
		});

	};
})(jQuery);
{/code}