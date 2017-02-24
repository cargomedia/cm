<?php

use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Components\HierarchicalPath as Path;
use League\Uri\Components\Query;

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

        $path = new Path('/avatar');
        $query = new Query();

        if (null !== $email) {
            $path = $path->append(
                md5(strtolower(trim($email)))
            );
        }
        if (null !== $size) {
            $query = $query->merge(
                Query::createFromPairs(['s' => $size])
            );
        }
        if (null !== $default) {
            $query = $query->merge(
                Query::createFromPairs(['d' => $default])
            );
        }
        return (string) HttpUri::createFromString('https://secure.gravatar.com')
            ->withPath((string) $path)
            ->withQuery((string) $query);
    }
}
