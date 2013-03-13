<?php

interface CM_File_ClassInterface {

	/**
	 * @return array ['class' => string, 'parent' => string|null]
	 * @throws CM_Exception
	 */
	public function getClassDeclaration();

	/**
	 * @return string
	 */
	public function getClassName();

	/**
	 * @return string
	 */
	public function getParentClassName();

}
