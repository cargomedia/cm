<?php

class CM_Tools_Generator_Application extends CM_Class_Abstract {

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
        $this->_filesystemHelper = new CM_Tools_Generator_FilesystemHelper($output);
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function addModule($name, $path) {
        $moduleDirectory = new CM_File($path, $this->_installation->getFilesystem());
        $this->_filesystemHelper->createDirectory($moduleDirectory);
        $configAdditions = array(
            'extra' => array(
                'cm-modules' => array(
                    $name => array(
                        'path' => $path,
                    ),
                ),
            ),
        );
        $this->_writeToComposerFile($configAdditions);
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function addNamespace($name, $path) {
        $namespaceDirectory = new CM_File($path, $this->_installation->getFilesystem());
        $this->_filesystemHelper->createDirectory($namespaceDirectory);
        $configAdditions = array(
            'autoload' => array(
                'psr-0' => array(
                    $name . '_' => dirname($path) . '/',
                ),
            ),
        );
        $this->_writeToComposerFile($configAdditions);
    }

    /**
     * @param string $name
     * @throws CM_Exception_Invalid
     */
    public function setProjectName($name) {
        if (!$this->isValidProjectName($name)) {
            throw new CM_Exception_Invalid('Invalid project name needs to be in `vendor-name/project-name` format.');
        }
        $this->_writeToComposerFile(['name' => $name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isValidProjectName($name) {
        return (bool) preg_match('/[a-z-]+\/[a-z-]+/', $name);
    }

    /**
     * @param string $appName
     * @param string $domain
     * @throws CM_Exception_Invalid
     */
    public function configureDevEnvironment($appName, $domain) {
        if (!$this->isValidDomain($domain)) {
            throw new CM_Exception_Invalid('Invalid domain');
        }
        $searchReplaceFile = function (CM_File $file, array $replacements) {
            $content = $file->read();
            foreach ($replacements as $search => $replace) {
                if (!strstr($content, $search)) {
                    throw new CM_Exception_Invalid("Cannot find `{$search}` in `{$file->getPath()}`");
                }
                $content = str_replace($search, $replace, $content);
            }
            $file->write($content);
        };

        $domainParts = explode('.', $domain);
        $tld = array_pop($domainParts);
        $dbName = str_replace('-', '_', $appName);

        $manifestFile = new CM_File('resources/config/default.php', $this->_installation->getFilesystem());
        $searchReplaceFile($manifestFile, [
            '<app-name-placeholder>' => $dbName,
        ]);

        $manifestFile = new CM_File('puppet/manifests/default.pp', $this->_installation->getFilesystem());
        $searchReplaceFile($manifestFile, [
            '<domain-placeholder>'   => $domain,
            '<app-name-placeholder>' => $appName,
        ]);

        $vagrantFile = new CM_File('Vagrantfile', $this->_installation->getFilesystem());
        $searchReplaceFile($vagrantFile, [
            '<tld-placeholder>'      => $tld,
            '<domain-placeholder>'   => $domain,
            '<app-name-placeholder>' => $appName,
        ]);
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function isValidDomain($domain) {
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/', $domain);
    }

    public function dumpAutoload() {
        $composer = $this->_installation->getComposer();
        /** @var \Composer\Repository\InstalledFilesystemRepository $localRepo */
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $package = $composer->getPackage();
        $config = $composer->getConfig();
        $config->merge([
            'config' => [
                'vendor-dir' => $this->_installation->getDirRoot() . $config->get('vendor-dir')
            ]
        ]);
        $im = $composer->getInstallationManager();
        $generator = $composer->getAutoloadGenerator();
        $generator->dump($config, $localRepo, $package, $im, 'composer');
    }

    /**
     * @param array $hash
     */
    private function _writeToComposerFile(array $hash) {
        $composerFile = new Composer\Json\JsonFile($this->_installation->getDirRoot() . 'composer.json');
        $configCurrent = $composerFile->read();

        $this->_filesystemHelper->notify('modify', 'composer.json');
        $configMerged = $this->_mergeConfigs($configCurrent, $hash);
        $composerFile->write($configMerged);
        $this->_installation->reload();
    }

    /**
     * @param array $configBase
     * @param array $configExtension
     * @return array
     */
    private function _mergeConfigs(array $configBase, array $configExtension) {
        foreach ($configExtension as $key => $overwriteElement) {
            if (array_key_exists($key, $configBase) && is_array($configBase[$key]) && is_array($overwriteElement)) {
                $configBase[$key] = $this->_mergeConfigs($configBase[$key], $overwriteElement);
            } else {
                $configBase[$key] = $overwriteElement;
            }
        }
        return $configBase;
    }
}
