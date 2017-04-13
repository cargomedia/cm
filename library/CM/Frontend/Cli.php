<?php

class CM_Frontend_Cli extends CM_Cli_Runnable_Abstract {

    const FAVICON_SVG_FILENAME = 'favicon.svg';
    const FAVICON_BACKGROUND_LESS_VARIABLE = 'colorFaviconBg';

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
            'square-180.png'                      => ['width' => 180, 'height' => 180],

            // Android Chrome
            'square-192.png'                      => ['width' => 192, 'height' => 192],
            'square-512.png'                      => ['width' => 512, 'height' => 512],

            // MS Tiles
            'tile-medium-270x270-transparent.png' => ['width' => 270, 'height' => 270, 'transparent' => true, 'iconSize' => 0.5],

            // Push Notification
            'push-notification-icon.png'          => ['width' => 192, 'height' => 192],
            'push-notification-badge.png'         => ['width' => 72, 'height' => 72, 'transparent' => true],
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
