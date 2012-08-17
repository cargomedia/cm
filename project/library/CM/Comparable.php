<?php 
interface CM_Comparable {
	/**
	 * @param CM_Comparable $other
	 * @return boolean
	 */
	public function equals(CM_Comparable $other = null);
}
