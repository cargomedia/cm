/*
 * Author: CM
 */
(function($) {
	var defaults = {
		start: 0,
		height: null,
		itemWidth: null,
		onSelect: function(e){}
	};

	$.carousel = function(e, options) {
		var self = this;
		this.$list = $(e).removeClass('carousel');
		
		this.options = $.extend({}, defaults, options || {});
		if (this.options.itemWidth === null) {
			this.options.itemWidth = this.$list.children(':eq(0)').outerWidth();
		}
		if (this.options.height === null) {
			this.options.height = this.$list.children(':eq(0)').outerHeight();
		}
		this.size = this.itemsVisible = this.$list.children().length;
		this.width = this.size * this.options.itemWidth;
		this.position = this.options.start;
		
		this.$clip = this.$list.wrap('<div class="carousel-clip">').parent().css({
			'position': 'relative',
			'overflow': 'hidden'
		});
		this.$container = this.$clip.wrap('<div class="carousel-cont">').parent().css({
			'position': 'relative',
			'padding-left': 20,
			'padding-right': 20
		});
		this.$list.css({
			'display': 'block',
			'width': this.width,
			'top': 0,
			'padding': 0,
			'left': 0
		});
		$('<a href="javascript:;" class="carousel-nav carousel-navPrev icon-arrow-left"> </a>').prependTo(this.$container).css({
			'position': 'absolute',
			'top': 0,
			'left': 0,
			'width': 20,
			'height': this.options.height
		}).click(function(){ self.prevFast(); });
		$('<a href="javascript:;" class="carousel-nav carousel-navNext icon-arrow-right"> </a>').appendTo(this.$container).css({
			'position': 'absolute',
			'top': 0,
			'right': 0,
			'width': 20,
			'height': this.options.height
		}).click(function(){ self.nextFast(); });
		
		$(document).unbind('keydown.carousel').bind('keydown.carousel', function(e) {
			if (!$(e.target).is(':input')) {
				if (e.which == 37) { // Arrow left
					self.prev();
				} else if (e.which == 39) { // Arrow right
					self.next();
				}
			}
		});
		
		this.repaint();
	};

	$.carousel.fn = $.carousel.prototype;
	$.carousel.fn.extend = $.extend;
	
	$.carousel.fn.extend({
		select: function(i, e) {
			if (i == this.position) {
				return false;
			}
			this.position = i;
			$item = this.$list.children(':eq('+this.position+')');
			this.$list.children('.active').removeClass('active');
			$item.addClass('active');			
			this.options.onSelect.call($item, e);
			this.repaint(true);
		},
		repaint: function(animate) {
			var clipWidth = this.$clip.width();
			this.$list.stop();
			this.$container.find('.carousel-nav').hide();
			if (clipWidth >= this.width) {
				this.itemsVisible = this.size;
				this.$list.css({
					'margin-left': 'auto',
					'margin-right': 'auto'
				});
			} else {
				this.itemsVisible = Math.floor(clipWidth / this.options.itemWidth);
				
				var leftShould = clipWidth / 2 - (this.options.itemWidth / 2);
				var left = this.position * this.options.itemWidth;
				
				var leftClip = left - leftShould;
				if (leftClip < 0) {
					leftClip = 0;
				} else {
					this.$container.find('.carousel-navPrev').show();
				}
				
				var rightClip = this.width - leftClip - clipWidth;
				if (rightClip < 0) {
					leftClip += rightClip;
				} else {
					this.$container.find('.carousel-navNext').show();
				}
				
				this.$list.animate({'margin-left': -1*leftClip}, (animate ? 200 : 0));
			}
		},
		next: function() {
			var i = this.position + 1;
			if (i >= this.size) i = 0;
			this.select(i);
		},
		prev: function() {
			var i = this.position - 1;
			if (i < 0) i = this.size - 1;
			this.select(i);
		},
		nextFast: function() {
			var i = this.position + Math.ceil(this.itemsVisible / 2);
			if (this.position == this.size-1) i = 0;
			if (i >= this.size) i = this.size-1;
			this.select(i);
		},
		prevFast: function() {
			var i = this.position - Math.ceil(this.itemsVisible / 2);
			if (this.position == 0) i = this.size-1;
			if (i < 0) i = 0;
			this.select(i);
		}
	});


	$.fn.carousel = function(o) {
		return this.each(function() {
			$(this).data('carousel',  new $.carousel(this, o));
		});
	};
})(jQuery);
