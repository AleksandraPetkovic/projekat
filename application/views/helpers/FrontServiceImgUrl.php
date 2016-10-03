<?php

class Zend_View_Helper_FrontServiceImgUrl extends Zend_View_Helper_Abstract
{
    public function frontServiceImgUrl($service){
        
        $serviceImgFileName = $service['id'] . '.jpg';
        //putanja do fajla
        $serviceImgFilePath = PUBLIC_PATH . '/uploads/service-photos/' . $serviceImgFileName;

        return $this->view->baseUrl('/uploads/service-photos/' . $serviceImgFileName);

    }
}

