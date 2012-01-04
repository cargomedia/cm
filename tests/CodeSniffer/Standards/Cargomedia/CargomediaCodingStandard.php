<?php

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

class PHP_CodeSniffer_Standards_Cargomedia_CargomediaCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard {

	/**
	 * Return a list of external sniffs to include with this standard.
	 *
	 * The Zend standard uses some PEAR sniffs.
	 *
	 * @return array
	 */
	public function getIncludedSniffs() {
		return array(
			'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
			'PEAR/Sniffs/Classes/ClassDeclarationSniff.php',
			'PEAR/Sniffs/ControlStructures/ControlSignatureSniff.php',
			'PEAR/Sniffs/Files/LineEndingsSniff.php',
			'PEAR/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php',
			'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',
			'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',
			'Squiz/Sniffs/Functions/GlobalFunctionSniff.php',
			'Zend/Sniffs/Files/ClosingTagSniff.php',
			'Zend/Sniffs/NamingConventions/ValidVariableNameSniff.php',
		);

	}
}
