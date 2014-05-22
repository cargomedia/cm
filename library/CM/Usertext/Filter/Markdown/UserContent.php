<?php

class CM_Usertext_Filter_Markdown_UserContent implements CM_Usertext_Filter_Interface, CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager = null) {
        if (null === $serviceManager) {
            $serviceManager = CM_Service_Manager::getInstance();
        }
        $this->setServiceManager($serviceManager);
    }

    public function transform($text, CM_Render $render) {
        $text = (string) $text;
        $text = preg_replace_callback('#!\[usercontent\]\(([^\]]+)\)#m', function ($matches) {
            $relativeUrl = trim($matches[1]);
            $userFile = new CM_File_UserContent('usercontent', $relativeUrl, null, $this->getServiceManager());
            return '<img src="' . $userFile->getUrl() . '" alt="image"/>';
        }, $text);

        return $text;
    }
}
