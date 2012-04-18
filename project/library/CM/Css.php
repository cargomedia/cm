<?php
require_once DIR_LIBRARY . 'lessphp/lessc.inc.php';

class CM_Css {

	/**
	 * @var string|null
	 */
	private $_css = null;
	/**
	 * @var string|null
	 */
	private $_prefix = null;
	/**
	 * @var CM_Css[]
	 */
	private $_children = array();

	/**
	 * @param string|null $css
	 * @param string|null $prefix
	 */
	public function __construct($css = null, $prefix = null) {
		if (!is_null($css)) {
			$this->_css = (string) $css;
		}
		if (!is_null($prefix)) {
			$this->_prefix = (string) $prefix;
		}
	}

	/**
	 * @param string      $css
	 * @param string|null $prefix
	 */
	public function add($css, $prefix = null) {
		$this->_children[] = new CM_Css($css, $prefix);
	}

	/**
	 * @param CM_Render $render
	 * @return string
	 */
	public function compile(CM_Render $render) {
		$mixins = <<< 'EOD'
.opacity(@opacity) when (isnumber(@opacity)) {
	opacity: @opacity;
	@ieOpacity = @opacity*100;
	filter:e("alpha(opacity=@{ieOpacity})");
}
.gradient(@direction, @color1, @color2) when (@direction = horizontal) and (iscolor(@color1)) and (iscolor(@color2)) {
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=1,startColorstr=rgbahex(@color1),endColorstr=rgbahex(@color2));
	background-image: linear-gradient(left,@color1,@color2);
	background-image: -moz-linear-gradient(left,@color1,@color2);
	background-image: -webkit-linear-gradient(left,@color1,@color2);
	background-image: -o-linear-gradient(left,@color1,@color2);
	background-image: -ms-linear-gradient(left,@color1,@color2);
}
.gradient(@direction, @color1, @color2) when (@direction = vertical) and (iscolor(@color1)) and (iscolor(@color2)) {
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=rgbahex(@color1),endColorstr=rgbahex(@color2));
	background-image: linear-gradient(top,@color1,@color2);
	background-image: -moz-linear-gradient(top,@color1,@color2);
	background-image: -webkit-linear-gradient(top,@color1,@color2);
	background-image: -o-linear-gradient(top,@color1,@color2);
	background-image: -ms-linear-gradient(top,@color1,@color2);
}
.background-color(@color) when (iscolor(@color)) and (alpha(@color) < 1) {
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=rgbahex(@color),endColorstr=rgbahex(@color));
	background-color: @color;
}
.background-color(@color) when not (iscolor(@color)), (iscolor(@color)) and (alpha(@color) = 1)  {
	background-color: @color;
}
.box-shadow(@args...) {
	box-shadow: @args;
	-webkit-box-shadow: @args;
}
.box-sizing(@args...) {
	box-sizing: @args;
	-moz-box-sizing: @args;
	-webkit-box-sizing: @args;
}
.user-select(@args...) {
	user-select: @args;
	-moz-user-select: @args;
	-ms-user-select: @args;
	-webkit-user-select: @args;
}
.transform(@args...) {
	transform: @args;
	-moz-transform: @args;
	-o-transform: @args;
	-ms-transform: @args;
	-webkit-transform: @args;
}
.transition(@args...) {
	transition: @args;
	-moz-transition: @args;
	-webkit-transition: @args;
}
EOD;
		$lessc = new lessc();
		$lessc->registerFunction('image', function ($arg) use($render) {
			list($type, $path) = $arg;
			return array($type, 'url(' . $render->getUrlImg(substr($path, 1, -1)) . ')');
		});
		$lessc->registerFunction('rgbahex', function($color) {
			if ($color[0] != 'color')
				throw new CM_Exception_Invalid("color expected for rgbahex");
			return sprintf("#%02x%02x%02x%02x",
				isset($color[4]) ? $color[4]*255 : 255,
				$color[1],$color[2], $color[3]);
		});
		$css = $mixins . $this;
		$cachKey = CM_CacheConst::Css . '_md5:' . md5($css);
		if (($parsedCss = CM_CacheLocal::get($cachKey)) === false) {
			$parsedCss = $lessc->parse($css);
			CM_CacheLocal::set($cachKey, $parsedCss);
		}
		return $parsedCss;
	}

	public function __toString() {
		$content = '';
		if ($this->_prefix) {
			$content .= $this->_prefix . ' {' . PHP_EOL;
		}
		if ($this->_css) {
			$content .= $this->_css . PHP_EOL;
		}
		foreach ($this->_children as $css) {
			$content .= $css;
		}
		if ($this->_prefix) {
			$content .= '}' . PHP_EOL;
		}
		return $content;
	}
}
