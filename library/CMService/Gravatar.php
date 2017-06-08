<?php

use CM\Url\Url;

class CMService_Gravatar extends CM_Class_Abstract {

    /**
     * @param string|null $email
     * @param int|null    $size
     * @param string|null $default
     * @return string
     */
    public function getUrl($email, $size = null, $default = null) {
        if (null !== $email) {
            $email = (string) $email;
        }
        if (null !== $size) {
            $size = (int) $size;
        }
        if (null !== $default) {
            $default = (string) $default;
        }
        if ((null === $email) && (null !== $default)) {
            return $default;
        }

        $path = '/avatar';
        $query = [];

        if (null !== $email) {
            $path .= '/' . (
                md5(strtolower(trim($email)))
            );
        }
        if (null !== $size) {
            $query['s'] = $size;
        }
        if (null !== $default) {
            $query['d'] = $default;
        }
        return (string) Url::createWithParams('https://secure.gravatar.com', $query)->withPath((string) $path);
    }
}
