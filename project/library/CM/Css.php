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
	 * @param CM_Render      $render
	 * @param boolean|null   $skipCompression
	 * @return string
	 */
	public function compile(CM_Render $render, $skipCompression = null) {
		$mixins = <<< 'EOD'
.opacity(@opacity) when (isnumber(@opacity)) {
	opacity: @opacity;
	@ieOpacity = @opacity*100;
	filter:e("alpha(opacity=@{ieOpacity})");
}
.gradient(@direction, @color1, @color2, @pos1: 0%, @pos2: 100%) when (@direction = horizontal) and (iscolor(@color1)) and (iscolor(@color2)) {
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=1,startColorstr=rgbahex(@color1),endColorstr=rgbahex(@color2));
	background-image: linear-gradient(left,@color1 @pos1,@color2 @pos2);
	background-image: -moz-linear-gradient(left,@color1 @pos1,@color2 @pos2);
	background-image: -webkit-linear-gradient(left,@color1 @pos1,@color2 @pos2);
	background-image: -o-linear-gradient(left,@color1 @pos1,@color2 @pos2);
	background-image: -ms-linear-gradient(left,@color1 @pos1,@color2 @pos2);
	background-image: -webkit-gradient(linear,left top,right top,color-stop(@pos1, @color1),color-stop(@pos2, @color2));
}
.gradient(@direction, @color1, @color2, @pos1: 0%, @pos2: 100%) when (@direction = vertical) and (iscolor(@color1)) and (iscolor(@color2)) {
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=rgbahex(@color1),endColorstr=rgbahex(@color2));
	background-image: linear-gradient(top,@color1 @pos1,@color2 @pos2);
	background-image: -moz-linear-gradient(top,@color1 @pos1,@color2 @pos2);
	background-image: -webkit-linear-gradient(top,@color1 @pos1,@color2 @pos2);
	background-image: -o-linear-gradient(top,@color1 @pos1,@color2 @pos2);
	background-image: -ms-linear-gradient(top,@color1 @pos1,@color2 @pos2);
	background-image: -webkit-gradient(linear,left top,left bottom,color-stop(@pos1, @color1),color-stop(@pos2, @color2));
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
			/** @var CM_Render $render */
			list($type, $delimiter, $values) = $arg;
			return array('function', 'url', array('string', $delimiter, array($render->getUrlResource('img', $values[0]))));
		});
		$lessc->registerFunction('urlFont', function ($arg) use($render) {
			/** @var CM_Render $render */
			list($type, $delimiter, $values) = $arg;
			return array($type, $delimiter, array($render->getUrlStatic('/font/' . $values[0])));
		});
		$css = $mixins . $this;
		$cacheKey = CM_CacheConst::Css . '_md5:' . md5($css);
		if ($render->getLanguage()) {
			$cacheKey .= '_languageId:' . $render->getLanguage()->getId();
		}
		if (($parsedCss = CM_CacheLocal::get($cacheKey)) === false) {
			if (!$render->isDebug() && !$skipCompression) {
				$lessc->setFormatter('compressed');
			}
			$parsedCss = $lessc->compile($css);
			CM_CacheLocal::set($cacheKey, $parsedCss);
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
