<?php

class Zend_View_Helper_ServiceImgUrl extends Zend_View_Helper_Abstract
{
    public function serviceImgUrl($service){
        
        $serviceImgFileName = $service['id'] . '.jpg';
        //putanja do fajla
        $serviceImgFilePath = PUBLIC_PATH . '/front/services/' . $serviceImgFileName;
        //Helper ima property view koji je Zend_View
        //i preko kojeg pozivamo ostale view helpere
        //npr $this->view->baseUrl()
        
        if(is_file($serviceImgFilePath)) {
            return $this->view->baseUrl('/front/services/' . $serviceImgFileName);
        }else{
            return $this->view->baseUrl('/front/services/no-image.jpg');
        }
    }
}

