<?php

class CM_File_Csv extends CM_File {

    /**
     * @return string[]
     */
    public function getHeader() {
        return $this->_convertStringToRow($this->readFirstLine());
    }

    /**
     * @param string[] $row
     */
    public function prependRow(array $row) {
        $content = $this->_convertRowToString($row) . $this->read();
        $this->write($content);
    }

    /**
     * @param string[] $row
     */
    public function appendRow(array $row) {
        $line = $this->_convertRowToString($row);
        $this->append($line);
    }

    /**
     * @param string[] $header
     * @return string[]
     */
    public function mergeHeader(array $header) {
        $headerOld = $this->getHeader();
        if (!$headerOld) {
            $headerOld = array();
        }
        $header = array_values($header);

        $header = array_merge($headerOld, $header);
        $header = array_unique($header);

        $headerNew = array_diff($header, $headerOld);
        if ($headerNew) {
            $missingCommas = str_repeat(',', count($headerNew));
            $content = preg_replace('/\n/', $missingCommas . '$0', $this->read());
            $content = preg_replace('/^.*\n/', $this->_convertRowToString($header), $content, 1);
            $this->write($content);
        }
        return $header;
    }

    /**
     * @param string|null $lineBreak
     * @param string|null $delimeter
     * @param string|null $enclosure
     * @param string|null $escape
     * @param int|null $linesToSkip
     * @return array
     * @throws CM_Exception_Invalid
     */
    public function parse($lineBreak = null, $delimeter = null, $enclosure = null, $escape = null, $linesToSkip = null) {
        $lineBreak = $lineBreak !== null ? (string) $lineBreak : "\n";
        $delimeter = $delimeter !== null ? (string) $delimeter : ',';
        $enclosure = $enclosure !== null ? (string) $enclosure : '"';
        $escape = $escape !== null ? (string) $escape : "\\";
        $linesToSkip = $linesToSkip !== null ? (int) $linesToSkip : 1;

        $fileContent = $this->read();
        $lineList = explode($lineBreak, $fileContent);
        if (empty($lineList)) {
            throw new CM_Exception_Invalid('Empty or bad CSV content');
        }
        $result = [];
        $i = 0;
        foreach ($lineList as $line) {
            $line = trim($line);
            if ($i++ < $linesToSkip || empty($line)) {
                continue;
            }
            $result[] = str_getcsv($line, $delimeter, $enclosure, $escape);
        }
        return $result;
    }

    /**
     * @param string[] $row
     * @return string
     */
    private function _convertRowToString(array $row) {
        $resource = fopen('php://memory', 'w+');
        fputcsv($resource, $row);
        rewind($resource);
        $line = fgets($resource);
        fclose($resource);
        return $line;
    }

    /**
     * @param string $line
     * @return string[]
     */
    private function _convertStringToRow($line) {
        $resource = fopen('php://memory', 'w+');
        fputs($resource, $line);
        rewind($resource);
        $row = fgetcsv($resource);
        fclose($resource);
        return $row;
    }
}
