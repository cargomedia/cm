<?php

namespace CM\Url\Components;

use League\Uri\Interfaces\Path;
use League\Uri\Components\HierarchicalPath;

class PrefixedPath extends HierarchicalPath {

    /** @var HierarchicalPath */
    private $_prefix;

    /**
     * @param Path|string|null $path
     * @param Path|string|null $prefix
     */
    public function __construct($path = null, $prefix = null) {
        $this->_prefix = new HierarchicalPath((string) $prefix);
        parent::__construct((string) $path);
    }

    /**
     * @param Path|string $prefix
     * @return PrefixedPath
     */
    public function withPrefix($prefix) {
        return new static($this->getContentWithoutPrefix(), $prefix);
    }

    /**
     * @return PrefixedPath
     */
    public function withoutPrefix() {
        return new static($this->getContentWithoutPrefix());
    }

    /**
     * @return bool
     */
    public function hasPrefix() {
        return '' !== $this->getPrefix();
    }

    /**
     * @return string
     */
    public function getPrefix() {
        return (string) $this->_prefix;
    }

    /**
     * @return null|string
     */
    public function getContentWithoutPrefix() {
        return parent::getContent();
    }

    public function getContent() {
        $data = $this->data;
        $front_delimiter = '';
        if ($this->isAbsolute === self::IS_ABSOLUTE) {
            $front_delimiter = static::$separator;
        }
        if ($this->hasPrefix()) {
            $front_delimiter = static::$separator;
            if ('' === $this->getContentWithoutPrefix()) {
                $data = $this->_prefix->getSegments();
            } else {
                $data = array_merge($this->_prefix->getSegments(), $data);
            }
        }
        return $this->encodePath($front_delimiter . implode(static::$separator, $data));
    }

    public function prepend($component) {
        return $this
            ->createFromSegments(
                $this->validateComponent($component),
                $this->isAbsolute
            )
            ->withPrefix($this->getPrefix())
            ->append($this);
    }

    public function append($component) {
        return new static((string) parent::append($component), $this->getPrefix());
    }

    protected function newCollectionInstance(array $data) {
        return new static((string) parent::newCollectionInstance($data), $this->getPrefix());
    }
}
