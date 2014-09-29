<?php

class CM_Tools_Generator_Config extends CM_Class_Abstract {

    /** @var CM_Tools_AppInstallation */
    private $_installation;

    /** @var CM_Tools_Generator_FilesystemHelper */
    private $_filesystemHelper;

    /**
     * @param CM_Tools_AppInstallation $appInstallation
     * @param CM_OutputStream_Interface $output
     */
    public function __construct(CM_Tools_AppInstallation $appInstallation, CM_OutputStream_Interface $output) {
        $this->_installation = $appInstallation;
        $this->_filesystemHelper = new CM_Tools_Generator_FilesystemHelper($this->_installation->getFilesystem(), $output);
    }

    public function addEntries(CM_File $configFile, CM_Config_Node $configNode) {
        $oldConfigNode = $this->_getConfigNode($configFile);
        $configNode = $this->_filter($oldConfigNode, $configNode);
        $this->_appendEntries($configFile, $configNode);
    }

    /**
     * @param CM_Config_Node $config
     * @param CM_Config_Node $configOverwrite
     * @return CM_Config_Node
     */
    private function _filter(CM_Config_Node $config, CM_Config_Node $configOverwrite) {
        $configNew = new CM_Config_Node();
        foreach (get_object_vars($configOverwrite) as $key => $value) {
            if (!isset($config->$key)) {
                $configNew->$key = $value;
            } elseif ($value instanceof CM_Config_Node) {
                $configNew->$key = $this->_filter($config->$key, $value);
            }
        }
        return $configNew;
    }

    /**
     * @param CM_File        $configFile
     * @param CM_Config_Node $config
     */
    private function _appendEntries(CM_File $configFile, CM_Config_Node $config) {
        $declarations = $this->_getConfigContent($configFile);
        $exportedValues = $this->_export($config);
        if (!$exportedValues) {
            return;
        }
        $declarations .= '    ' . PHP_EOL;
        foreach ($exportedValues as $key => $value) {
            $valueBlock = new \CodeGenerator\ValueBlock($value);
            $declarations .= '    $config->' . $key . ' = ' . $valueBlock->dump() . ';' . PHP_EOL;
        }

        $functionBlock = new \CodeGenerator\FunctionBlock();
        $functionBlock->setCode($declarations);
        $functionBlock->addParameter(new \CodeGenerator\ParameterBlock('config', 'CM_Config_Node'));

        $content = join(PHP_EOL, ['<?php', '', 'return ' . $functionBlock->dump() . ';']);
        $configFile->ensureParentDirectory();
        $configFile->write($content);
        $this->_filesystemHelper->createFile($configFile, $content, true);
    }

    /**
     * @param CM_Config_Node $config
     * @param bool|null      $hasParent
     * @return array
     */
    private function _export(CM_Config_Node $config, $hasParent = null) {
        $keys = [];
        $vars = get_object_vars($config);
        foreach ($vars as $key => $value) {
            if ($value instanceof CM_Config_Node) {
                $localKeys = $this->_export($value, true);
                foreach ($localKeys as $localKey => $localValue) {
                    $keys[$key . '->' . $localKey] = $localValue;
                }
            } else {
                $keys[$key] = $value;
            }
        }
        return $keys;
    }

    /**
     * @param CM_File $configFile
     * @return CM_Config_Node
     */
    private function _getConfigNode(CM_File $configFile) {
        $configNode = new CM_Config_Node();
        $configExtend = $this->_getConfigClosure($configFile);
        if ($configExtend) {
            $configExtend($configNode);
        }
        return $configNode;
    }

    /**
     * @param CM_File $configFile
     * @return Closure|null
     * @throws CM_Exception_Invalid
     */
    private function _getConfigClosure(CM_File $configFile) {
        if (!$configFile->getExists()) {
            return null;
        }
        $configSetter = require $configFile->getPath();
        if (!$configSetter instanceof Closure) {
            throw new CM_Exception_Invalid('Invalid config file. `' . $configFile->getPath() . '` must return closure');
        }
        return $configSetter;
    }

    /**
     * @param CM_File $configFile
     * @return string
     */
    private function _getConfigContent(CM_File $configFile) {
        $closure = $this->_getConfigClosure($configFile);
        if (!$closure) {
            return '';
        }
        $reflectionFunction = new ReflectionFunction($closure);
        $lines = explode(PHP_EOL, $configFile->read());
        $startLine = $reflectionFunction->getStartLine();
        $length = $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() - 1;
        $codeLines = array_slice($lines, $startLine, $length);
        $code = join(PHP_EOL, $codeLines);
        $code = preg_replace('/^\s*[\r\n]+/', '', $code);
        $code = preg_replace('/[\r\n]+\s*$/', '', $code);
        $code .= PHP_EOL;
        return $code;
    }
}
