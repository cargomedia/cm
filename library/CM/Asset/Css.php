<?php

class CM_Asset_Css extends CM_Asset_Abstract {

	/** @var CM_Render */
	protected $_render;

	/** @var string|null */
	protected $_content;

	/** @var string|null */
	private $_prefix;

	/** @var CM_Asset_Css[] */
	private $_children = array();

	/**
	 * @param CM_Render   $render
	 * @param string|null $content
	 * @param string|null $prefix
	 */
	public function __construct(CM_Render $render, $content = null, $prefix = null) {
		$this->_render = $render;
		if (null !== $content) {
			$this->_content = (string) $content;
		}
		if (null !== $prefix) {
			$this->_prefix = (string) $prefix;
		}
	}

	/**
	 * @param string      $content
	 * @param string|null $prefix
	 */
	public function add($content, $prefix = null) {
		$this->_children[] = new self($this->_render, $content, $prefix);
	}

	public function get($compress = null) {
		$content = $this->_getContent();
		if ($compress) {
			return $this->_compile($content, true);
		} else {
			return $this->_compile($content);
		}
	}

	protected function _getContent() {
		$content = '';
		if ($this->_prefix) {
			$content .= $this->_prefix . ' {' . PHP_EOL;
		}
		if ($this->_content) {
			$content .= $this->_content . PHP_EOL;
		}
		foreach ($this->_children as $css) {
			$content .= $css->_getContent();
		}
		if ($this->_prefix) {
			$content .= '}' . PHP_EOL;
		}
		return $content;
	}

	/**
	 * @param string       $content
	 * @param boolean|null $compress
	 * @return string
	 */
	private function _compile($content, $compress = null) {
		$content = (string) $content;
		$compress = (bool) $compress;
		$render = $this->_render;

		$cacheKey = CM_CacheConst::App_Resource . '_md5:' . md5($content);
		$cacheKey .= '_compress:' . (int) $compress;
		if ($render->getLanguage()) {
			$cacheKey .= '_languageId:' . $render->getLanguage()->getId();
		}
		if (false === ($contentTransformed = CM_Cache_File::get($cacheKey))) {
			$lessCompiler = new lessc();
			$render = $this->_render;
			$lessCompiler->registerFunction('image', function ($arg) use ($render) {
				/** @var CM_Render $render */
				list($type, $delimiter, $values) = $arg;
				return array('function', 'url', array('string', $delimiter, array($render->getUrlResource('layout', 'img/' . $values[0]))));
			});
			$lessCompiler->registerFunction('urlFont', function ($arg) use ($render) {
				/** @var CM_Render $render */
				list($type, $delimiter, $values) = $arg;
				return array($type, $delimiter, array($render->getUrlStatic('/font/' . $values[0])));
			});
			if ($compress) {
				$lessCompiler->setFormatter('compressed');
			}
			$contentTransformed = $lessCompiler->compile($this->_getMixins() . $content);
			CM_Cache_File::set($cacheKey, $contentTransformed);
		}
		return $contentTransformed;
	}

	/**
	 * @return string
	 */
	private function _getMixins() {
		$mixins = <<< 'EOD'
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
		return $mixins;
	}
}
