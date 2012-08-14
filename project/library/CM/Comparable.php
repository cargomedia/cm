<?php 
interface CM_Comparable {
	/**
	 * @param mixed $other
	 * @return boolean
	 */
	public function equals(CM_Comparable $other = null);
}
