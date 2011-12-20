<?php

abstract class CM_ModelAsset_Entity_Abstract extends CM_ModelAsset_Abstract {
	/**
	 * @var CM_Model_Entity_Abstract
	 */
	protected $_model;

	/**
	 * @param CM_Model_Entity_Abstract $entity
	 */
	public function __construct(CM_Model_Entity_Abstract $entity) {
		parent::__construct($entity);
	}

}
