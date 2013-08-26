<?php

interface CM_View_AccessRestrictable {

	/**
	 * @throws CM_Exception_AuthRequired
	 * @throws CM_Exception_Nonexistent
	 */
	public function checkAccessible();
}
