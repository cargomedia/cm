<?php 
interface CM_Comparable {
	/**
	 * @param mixed $other
	 * @return boolean
	 */
	public function equals(self $other = null);
}
