<?php

class CM_ExceptionHandling_Formatter_Plain extends CM_ExceptionHandling_Formatter_Abstract {

  public function getHeader(CM_ExceptionHandling_SerializableException $exception) {
    return $exception->getClass() . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . PHP_EOL;
  }

  public function getTrace(CM_ExceptionHandling_SerializableException $exception) {
    $traceString = '';
    $indent = strlen(count($exception->trace)) + 4;
    foreach ($exception->trace as $number => $entry) {
      $traceString .= str_pad($number, $indent, ' ', STR_PAD_LEFT) . '. ';
      $traceString .= $entry['code'] . ' ' . $entry['file'];
      if (null !== $entry['line']) {
        $traceString .= ':' . $entry['line'];
      }
      $traceString .= PHP_EOL;
    }
    return $traceString;
  }
}
