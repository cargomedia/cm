<?php

namespace CM\Url\Modifiers;

use CM_Util;
use InvalidArgumentException;
use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AbstractUriModifier;

class Sanitize extends AbstractUriModifier {

    public function __invoke($uri) {
        if (!$uri instanceof Http) {
            throw new InvalidArgumentException('URI passed must be a League\Uri\Schemes\Http instance');
        }
        return $uri
            ->withHost(CM_Util::sanitizeUtf($uri->getHost()))
            ->withPath(CM_Util::sanitizeUtf($uri->getPath()))
            ->withQuery(CM_Util::sanitizeUtf($uri->getQuery()))
            ->withFragment(CM_Util::sanitizeUtf($uri->getFragment()));
    }
}
