<?php

class CM_Frontend_TemplateHelper_RatioKeeper {

    public static function create($params) {
        if (isset($params['width']) && isset($params['height'])) {
            $width = (int) $params['width'];
            $height = (int) $params['height'];
        } elseif (isset($params['ratio'])) {
            $ratio = $params['ratio'];
            $width = 100;
            $height = $width * $ratio;
        } else {
            $width = $height = 1;
        }

        $cache = CM_Cache_Local::getInstance();
        $imageData = $cache->get($cache->key('_ratioKeeper:', $width, $height), function () use ($width, $height) {
            $image = imagecreate($width, $height);
            ob_start();
            imagecolorallocate($image, 255, 255, 255);
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();
            return $imageData;
        });
        $imageSrc = 'data:image/png;base64,' . base64_encode($imageData);

        $output = '<div class="ratioKeeper' . ((isset($params['stretch']) && $params['stretch']) ? ' stretch' : '') . '">';
        $output .= '<img class="ratioKeeper-size" src="' . $imageSrc . '">';

        $contentClass = 'ratioKeeper-content';
        if (isset($params['contentClass'])) {
            $contentClass .= ' ' . $params['contentClass'];
        }

        $output .= '<div class="' . $contentClass . '">';
        $output .= $params['content']. '</div>';

        $output .= '</div>';
        return $output;
    }
}
