<?php

class CM_Javascript_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string|null $namespace
     */
    public function browserify($namespace = null) {
        if (null == $namespace) {
            $this->_getStreamError()->writeln('--namespace=<NAMESPACE> option required.');
            return 1;
        }

        $this->_getStreamOutput()->writeln('Convert javascript library code to support browserify...');
        foreach (CM_Util::rglobLibrariesByModule('*.js', $namespace) as $path) {
            $file = new CM_File($path);
            $content = $file->read();
            $shortPath = preg_replace('/^.*(' . $namespace . ')/', '$1', $path);
            $matches = null;

            if (preg_match('/require\(/', $content)) {
                $this->_getStreamOutput()->writeln('done: ' . $shortPath);
            } elseif (preg_match('/var ([^ ]+) = ([^ \.]+)\.extend\({/', $content, $matches)) {
                $className = $matches[1];
                $extendClassName = $matches[2];
                $extendClassPath = preg_replace('/_/', '/', $matches[2]);

                $this->_getStreamOutput()->writeln('processing: ' . $shortPath . ' > class `' . $className . '` extend `' . $extendClassName . '`');
                $file->write(join(PHP_EOL, [
                    "var " . $extendClassName . " = require('" . $extendClassPath . "');",
                    "",
                    $content,
                    "",
                    "module.exports = " . $className . ";"
                ]));
            } else {
                $this->_getStreamOutput()->writeln('not supported: ' . $shortPath);
            }
        }
    }

    public static function getPackageName() {
        return 'js';
    }
}
