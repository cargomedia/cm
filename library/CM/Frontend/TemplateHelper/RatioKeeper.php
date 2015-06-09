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
        $imageData = $cache->get($cache->key(CM_CacheConst::Frontend_TemplateHelper_RatioKeeper, $width, $height), function () use ($width, $height) {
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
        $output .= $params['content'];

        $output .= '</div>';
        return $output;
    }
}
