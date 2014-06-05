<?php

class CM_Usertext_Filter_Markdown_UserContent implements CM_Usertext_Filter_Interface, CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager|null $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager = null) {
        if (null === $serviceManager) {
            $serviceManager = CM_Service_Manager::getInstance();
        }
        $this->setServiceManager($serviceManager);
    }

    public function transform($text, CM_Render $render) {
        $text = (string) $text;
        $text = preg_replace_callback('#!\[formatter\]\(([^\)]+)\)#m', function ($matches) {
            $filename = trim($matches[1]);
            $file = new CM_File_UserContent('formatter', $filename, null, $this->getServiceManager());
            return '![image](' . $file->getUrl() . ')';
        }, $text);

        return $text;
    }
}
