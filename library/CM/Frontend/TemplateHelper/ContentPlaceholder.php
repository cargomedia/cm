<?php

class CM_Frontend_TemplateHelper_ContentPlaceholder {

    /**
     * @param string     $content
     * @param int|null   $width
     * @param int|null   $height
     * @param bool|null  $stretch
     * @param array|null $color [R,G,B,A]
     * @return string
     */
    public static function create($content, $width = null, $height = null, $stretch = null, array $color = null) {
        if (null === $width) {
            $width = 1;
        }
        if (null === $height) {
            $height = 1;
        }
        if (null === $color) {
            $color = [255, 255, 255, 0];
        }
        $stretch = (bool) $stretch;
        $imageData = self::_createBlankImage($width, $height, $color);
        $imageSrc = 'data:image/png;base64,' . base64_encode($imageData);

        $output = '<div class="contentPlaceholder' . ($stretch ? ' stretch' : '') . '">';
        $output .= '<img class="contentPlaceholder-size" src="' . $imageSrc . '"/>';
        $output .= $content;

        $output .= '</div>';
        return $output;
    }

    /**
     * @param int   $width
     * @param int   $height
     * @param array $color [R,G,B,A]
     * @return string
     */
    private static function _createBlankImage($width, $height, array $color) {
        $cache = CM_Cache_Local::getInstance();

        return $cache->get($cache->key(__CLASS__, $width, $height, $color), function () use ($width, $height, $color) {
            $image = imagecreate($width, $height);
            ob_start();
            imagecolorallocatealpha($image, $color[0], $color[1], $color[2], $color[3]);
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();
            return $imageData;
        });
    }
}
