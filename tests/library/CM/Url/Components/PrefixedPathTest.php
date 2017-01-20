<?php

namespace CM\Test\Url\Components;

use CMTest_TestCase;
use CM\Url\Components\PrefixedPath;

class PrefixedPathTest extends CMTest_TestCase {

    public function testInstantiation() {
        $path = new PrefixedPath();
        $this->assertSame('', (string) $path);

        $path = new PrefixedPath(null, 'bar');
        $this->assertSame('/bar', (string) $path);

        $path = new PrefixedPath('foo');
        $this->assertSame('foo', (string) $path);

        $path = new PrefixedPath('/foo');
        $this->assertSame('/foo', (string) $path);

        $path = new PrefixedPath('foo', 'bar');
        $this->assertSame('/bar/foo', (string) $path);

        $path = new PrefixedPath('/foo', 'bar');
        $this->assertSame('/bar/foo', (string) $path);

        $path = new PrefixedPath('/foo', 'bar/baz');
        $this->assertSame('/bar/baz/foo', (string) $path);
    }

    public function testGetPrefix() {
        $path = new PrefixedPath('/foo');
        $this->assertSame('', $path->getPrefix());
        $path = new PrefixedPath('/foo', '');
        $this->assertSame('', $path->getPrefix());
        $path = new PrefixedPath('/foo', 'bar/baz');
        $this->assertSame('bar/baz', $path->getPrefix());
    }

    public function testHasPrefix() {
        $path = new PrefixedPath('/foo');
        $this->assertSame(false, $path->hasPrefix());
        $path = new PrefixedPath('/foo', '');
        $this->assertSame(false, $path->hasPrefix());
        $path = new PrefixedPath('/foo', 'bar/baz');
        $this->assertSame(true, $path->hasPrefix());
    }

    public function testWithPrefix() {
        $path = new PrefixedPath();
        $path = $path->withPrefix('bar');
        $this->assertSame('/bar', (string) $path);
        $this->assertSame(true, $path->hasPrefix());
        $this->assertSame('bar', $path->getPrefix());

        $path = new PrefixedPath('foo');
        $path = $path->withPrefix('bar');
        $this->assertSame('/bar/foo', (string) $path);
        $this->assertSame(true, $path->hasPrefix());
        $this->assertSame('bar', $path->getPrefix());

        $path = new PrefixedPath('/foo');
        $path = $path->withPrefix('bar');
        $this->assertSame('/bar/foo', (string) $path);
        $this->assertSame(true, $path->hasPrefix());
        $this->assertSame('bar', $path->getPrefix());

        $path = new PrefixedPath('/foo', 'bar');
        $path = $path->withPrefix('baz');
        $this->assertSame('/baz/foo', (string) $path);
        $this->assertSame(true, $path->hasPrefix());
        $this->assertSame('baz', $path->getPrefix());
    }

    public function testWithoutPrefix() {
        $path = new PrefixedPath();
        $path = $path->withoutPrefix();
        $this->assertSame('', (string) $path);
        $this->assertSame(false, $path->hasPrefix());

        $path = new PrefixedPath('foo');
        $path = $path->withoutPrefix();
        $this->assertSame('foo', (string) $path);
        $this->assertSame(false, $path->hasPrefix());

        $path = new PrefixedPath('/foo');
        $path = $path->withoutPrefix();
        $this->assertSame('/foo', (string) $path);
        $this->assertSame(false, $path->hasPrefix());

        $path = new PrefixedPath('/foo', 'bar');
        $path = $path->withoutPrefix();
        $this->assertSame('/foo', (string) $path);
        $this->assertSame(false, $path->hasPrefix());
    }

    public function testAppend() {
        $path = new PrefixedPath('/foo', 'bar');
        $path = $path->append('foz');

        $this->assertTrue($path->hasPrefix());
        $this->assertSame('bar', $path->getPrefix());
        $this->assertSame('/bar/foo/foz', (string) $path);
    }

    public function testPrepend() {
        $path = new PrefixedPath('/foo', 'bar');
        $path = $path->prepend('foz');

        $this->assertTrue($path->hasPrefix());
        $this->assertSame('bar', $path->getPrefix());
        $this->assertSame('/bar/foz/foo', (string) $path);
    }

    public function testFilter() {
        $path = new PrefixedPath('/foo/fox/foy', 'bar');
        $segments = [];
        /** @var PrefixedPath $path */
        $path = $path->filter(function ($segment) use (&$segments) {
            $segments[] = $segment;
            return 'fox' !== $segment;
        });

        $this->assertSame(['foo', 'fox', 'foy'], $segments);
        $this->assertSame('/bar/foo/foy', (string) $path);
        $this->assertTrue($path->hasPrefix());
        $this->assertSame('bar', $path->getPrefix());
        $this->assertSame('/foo/foy', (string) $path->getContentWithoutPrefix());
    }
}
