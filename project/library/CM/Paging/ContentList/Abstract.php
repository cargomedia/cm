<?php

class CM_Paging_ContentList_Abstract extends CM_Paging_Abstract {
	const TYPE_VIDEOSRC = 1;
	const TYPE_BADWORDS = 2;

	/**
	 * @var int
	 */
	private $_type;

	/**
	 * @param int $type
	 */
	public function __construct($type) {
		$this->_type = (int) $type;
		$source = new CM_PagingSource_Sql_Deferred('string', TBL_CM_STRING, '`type`=' . $this->_type);
		$source->enableCache();
		parent::__construct($source);
	}

	/**
	 * @param string $string
	 */
	public function add($string) {
		CM_Mysql::replace(TBL_CM_STRING, array('type' => $this->_type, 'string' => $string));
		$this->_change();
	}

	/**
	 * @param string $string
	 */
	public function delete($string) {
		CM_Mysql::delete(TBL_CM_STRING, array('type' => $this->_type, 'string' => $string));
		$this->_change();
	}

	/**
	 * @param string $string
	 * @param string $pattern OPTIONAL
	 * @return boolean
	 */
	public function contains($string, $pattern = '/^\Q$item\E$/i') {
		foreach ($this->getItems() as $item) {
			if (preg_match(str_replace('$item', $item, $pattern), $string)) {
				return true;
			}
		}
		return false;
	}
}
