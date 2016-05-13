<?php

class CM_Frontend_Cli extends CM_Cli_Runnable_Abstract {

    const FAVICON_SVG_FILENAME = 'favicon.svg';
    const FAVICON_BACKGROUND_LESS_VARIABLE = 'colorBrand';

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

    public function generateFavicon() {
        $faviconConfigList = $this->_getFaviconConfigList();
        $this->_getStreamOutput()->writeln('Generating favicons');

        $themeDirStructList = Functional\map(CM_Site_Abstract::getAll(), function (CM_Site_Abstract $site) {
            $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
            return [
                'render'   => $render,
                'themeDir' => new CM_File($render->getThemeDir(true)),
            ];
        });
        $themeDirStructList = Functional\unique($themeDirStructList, function (array $themeDirStruct) {
            /** @var CM_File $themeDir */
            $themeDir = $themeDirStruct['themeDir'];
            return $themeDir->getPath();
        });//filter site aliases

        foreach ($themeDirStructList as $themeDirStruct) {
            /** @var CM_Frontend_Render $render */
            $render = $themeDirStruct['render'];
            /** @var CM_File $themeDir */
            $themeDir = $themeDirStruct['themeDir'];
            $svgFile = $themeDir->joinPath('resource', 'img', self::FAVICON_SVG_FILENAME);
            if ($svgFile->exists()) {
                foreach ($faviconConfigList as $outputFilename => $config) {
                    $backgroundWidth = (int) $config['width'];
                    $backgroundHeight = (int) $config['height'];
                    $backgroundColor = false === $config['transparent'] ?
                        $render->getLessVariable(self::FAVICON_BACKGROUND_LESS_VARIABLE) :
                        'transparent';

                    $background = new Imagick();
                    $background->newPseudoImage($backgroundWidth, $backgroundHeight, 'canvas:' . $backgroundColor);
                    $backgroundImage = new CM_Image_Image($background);

                    $iconSize = (int) (min($backgroundWidth, $backgroundHeight) * (float) $config['iconSize']);
                    $iconImage = CM_Image_Image::createFromSVGWithSize($svgFile->read(), $iconSize, $iconSize);

                    $backgroundImage->compositeImage($iconImage, ($backgroundWidth - $iconSize) / 2, ($backgroundHeight - $iconSize) / 2);
                    $backgroundImage->setFormat(CM_Image_Image::FORMAT_PNG);

                    $targetFile = $themeDir->joinPath('resource', 'img', 'meta', $outputFilename);
                    $targetFile->ensureParentDirectory();
                    $targetFile->write($backgroundImage->getBlob());
                    $this->_getStreamOutput()->writeln('Generated ' . $targetFile->getPath());
                }
            }
        }
    }

    /**
     * @return array
     */
    private function _getFaviconConfigList() {
        $faviconConfigDefault = ['iconSize' => 1, 'transparent' => false];

        $configImageList = [
            // Favicon & Apple Touch Icons
            'square-16.png'                       => ['width' => 16, 'height' => 16],
            'square-32.png'                       => ['width' => 32, 'height' => 32],
            'square-76.png'                       => ['width' => 76, 'height' => 76],
            'square-96.png'                       => ['width' => 96, 'height' => 96],
            'square-120.png'                      => ['width' => 120, 'height' => 120],
            'square-144.png'                      => ['width' => 144, 'height' => 144],
            'square-152.png'                      => ['width' => 152, 'height' => 152],
            'square-167.png'                      => ['width' => 167, 'height' => 167],
            'square-180.png'                      => ['width' => 180, 'height' => 180],

            // Android Chrome
            'square-144-transparent.png'          => ['width' => 144, 'height' => 144, 'transparent' => true],
            'square-192-transparent.png'          => ['width' => 192, 'height' => 192, 'transparent' => true],
            'square-256-transparent.png'          => ['width' => 256, 'height' => 256, 'transparent' => true],
            'square-384-transparent.png'          => ['width' => 384, 'height' => 384, 'transparent' => true],
            'square-512-transparent.png'          => ['width' => 512, 'height' => 512, 'transparent' => true],

            // Splashscreens
            'splashscreen-1242x2208.png'          => ['width' => 1242, 'height' => 2208, 'iconSize' => 0.2],
            'splashscreen-750x1334.png'           => ['width' => 750, 'height' => 1334, 'iconSize' => 0.2],
            'splashscreen-1536x2008.png'          => ['width' => 1536, 'height' => 2008, 'iconSize' => 0.2],
            'splashscreen-748x1024.png'           => ['width' => 748, 'height' => 1024, 'iconSize' => 0.2],
            'splashscreen-640x1096.png'           => ['width' => 640, 'height' => 1096, 'iconSize' => 0.3],
            'splashscreen-640x920.png'            => ['width' => 640, 'height' => 920, 'iconSize' => 0.3],

            // MS Tiles
            'tile-small-128x128-transparent.png'  => ['width' => 128, 'height' => 128, 'transparent' => true, 'iconSize' => 0.5],
            'tile-medium-270x270-transparent.png' => ['width' => 270, 'height' => 270, 'transparent' => true, 'iconSize' => 0.5],
            'tile-large-558x558-transparent.png'  => ['width' => 558, 'height' => 558, 'transparent' => true, 'iconSize' => 0.5],
            'tile-wide-558x270-transparent.png'   => ['width' => 558, 'height' => 270, 'transparent' => true, 'iconSize' => 0.5],
        ];

        foreach ($configImageList as &$config) {
            $config = array_merge($faviconConfigDefault, $config);

            if (array_key_exists('iconSize', $config)) {
                $iconSize = (float) $config['iconSize'];
                if ($iconSize > 1 || $iconSize < 0) {
                    $config['iconSize'] = 1;
                }
            }
        }
        return $configImageList;
    }

    public static function getPackageName() {
        return 'frontend';
    }
}
