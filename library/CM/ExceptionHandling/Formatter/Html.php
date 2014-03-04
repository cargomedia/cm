<?php

class CM_ExceptionHandling_Formatter_Html extends CM_ExceptionHandling_Formatter_Abstract {

  public function getHeader(CM_ExceptionHandling_SerializableException $exception) {
    return '<h1>' . $exception->getClass() . '</h1><h2>' . $exception->getMessage() . '</h2><code>' . $exception->getFile() . ' on line ' .
    $exception->getLine() . '</pre>';
  }

  public function getTrace(CM_ExceptionHandling_SerializableException $exception) {
    $traceString = '<pre>';
    $indent = strlen(count($exception->trace)) + 4;
    foreach ($exception->trace as $number => $entry) {
      $traceString .= str_pad($number, $indent, ' ', STR_PAD_LEFT) . '. ';
      $traceString .= $entry['code'] . ' ' . $entry['file'];
      if (null !== $entry['line']) {
        $traceString .= ':' . $entry['line'];
      }
      $traceString .= PHP_EOL;
    }
    $traceString .= '</pre>';
    return $traceString;
  }
}
