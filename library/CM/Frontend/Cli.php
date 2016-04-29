<?php

class CM_Frontend_Cli extends CM_Cli_Runnable_Abstract {

    public function iconRefresh() {
        /** @var CM_File[] $svgFileList */
        $svgFileList = array();
        foreach (CM_Bootloader::getInstance()->getModules() as $moduleName) {
            $iconPath = CM_Util::getModulePath($moduleName) . 'layout/default/resource/img/icon/';
            foreach (glob($iconPath . '*.svg') as $svgPath) {
                $svgFile = new CM_File($svgPath);
                $svgFileList[strtolower($svgFile->getFileName())] = $svgFile;
            }
        }

        if (0 === count($svgFileList)) {
            throw new CM_Exception_Invalid('Cannot process `0` icons');
        }
        $this->_getStreamOutput()->writeln('Processing ' . count($svgFileList) . ' unique icons...');

        $dirWork = CM_File::createTmpDir();
        $dirBuild = $dirWork->joinPath('/build');

        foreach ($svgFileList as $fontFile) {
            $fontFile->copyToFile($dirWork->joinPath($fontFile->getFileName()));
        }

        CM_Util::exec('fontcustom', array(
            'compile', $dirWork->getPathOnLocalFilesystem(),
            '--no-hash',
            '--autowidth',
            '--font-name=icon-webfont',
            '--output=' . $dirBuild->getPathOnLocalFilesystem()
        ));

        $cssFile = $dirBuild->joinPath('/icon-webfont.css');
        $less = preg_replace('/url\("(?:.*?\/)(.+?)(\??#.+?)?"\)/', 'url(urlFont("\1") + "\2")', $cssFile->read());
        CM_File::create(DIR_PUBLIC . 'static/css/library/icon.less', $less);

        foreach (glob($dirBuild->joinPath('/icon-webfont.*')->getPathOnLocalFilesystem()) as $fontPath) {
            $fontFile = new CM_File($fontPath);
            $fontFile->rename(DIR_PUBLIC . 'static/font/' . $fontFile->getFileName());
        }

        $dirWork->delete(true);
        $this->_getStreamOutput()->writeln('Created web-font and stylesheet.');
    }

    public static function getPackageName() {
        return 'frontend';
    }
}
