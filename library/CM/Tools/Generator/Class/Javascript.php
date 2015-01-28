<?php

class CM_Tools_Generator_Class_Javascript extends CM_Tools_Generator_Class_Abstract {

    /**
     * @param string $className
     * @return CM_File
     */
    public function createClassFile($className) {
        $parentClassName = $this->getParentClassName($className);
        $content = array();
        $content[] = self::_getDocBlock(array('class' => $className, 'extends' => $parentClassName));
        $content[] = 'var ' . $className . ' = ' . $parentClassName . '.extend({';
        $content[] = '';
        if ($parentClassName === 'CM_View_Abstract' || is_subclass_of($parentClassName, 'CM_View_Abstract')) {
            $content[] = self::_getDoc('@type String', 1);
            $content[] = "\t_class: '" . $className . "'";
        }
        $content[] = '});';
        $content[] = '';
        $classFile = new CM_File($this->_getClassPath($className), $this->_appInstallation->getFilesystem());
        $this->_filesystemHelper->createFile($classFile, implode(PHP_EOL, $content));
    }

    /**
     * @param string $className
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _getClassPath($className) {
        $namespace = CM_Util::getNamespace($className);
        $namespacePath = $this->_appInstallation->getNamespacePath($namespace);
        return $namespacePath . '/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.js';
    }

    /**
     * @param array|string $docLines
     * @param int|null     $indentation
     * @return string
     */
    private static function _getDocBlock($docLines, $indentation = null) {
        $docLines = (array) $docLines;
        $indentation = (int) $indentation;
        $docBlock = '/**' . PHP_EOL;
        foreach ($docLines as $param => $value) {
            $docBlock .= str_repeat("\t", $indentation);
            $docBlock .= ' * ';
            if (is_string($param)) {
                $docBlock .= '@' . $param . ' ';
            }
            $docBlock .= $value;
            $docBlock .= PHP_EOL;
        }
        $docBlock .= ' */';
        return $docBlock;
    }

    /**
     * @param string   $doc
     * @param int|null $indentation
     * @return string
     */
    private static function _getDoc($doc, $indentation = null) {
        $indentation = str_repeat("\t", (int) $indentation);
        return $indentation . '/** ' . $doc . ' */';
    }
}
