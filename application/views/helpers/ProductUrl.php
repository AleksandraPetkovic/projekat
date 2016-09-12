<?php

class Zend_View_Helper_ProductUrl extends Zend_View_Helper_Abstract
{
    public function productUrl($product){
        
        return $this->view->url(array(
            'id' => $product['id'],
            'product_slug' => $product['product_name']
            
        ), 'product-route', true);
    }
}


