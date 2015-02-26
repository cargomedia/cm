<?php

function smarty_prefilter_translate($source, Smarty_Internal_Template $template) {
    $source = preg_replace('#{translate\s+#', '{translate key=', $source);
    return $source;
}
