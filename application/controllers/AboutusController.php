<?php

class AboutusController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        
        //select je objekat klase Zend_Db_Select
        $select = $cmsProductsDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsProducts::STATUS_ENABLED)
                ->order('order_number');
        
        //debug za  db select - vraca se sql upit
        //die($select->assemble());
        
        
        
        $sitemapPageId = (int) $request->getParam('sitemap_page_id');
        
        if($sitemapPageId <=0){
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $id, 404);
        }
        
        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        
        if(!$sitemapPage) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $id, 404);
        }
        
        if(
                $sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                //check if user is not logged in
                // then preview is not avaliable
                //for disabled pages
                && Zend_Auth::getInstance()->hasIdentity()
                
        ) {
            throw new Zend_Controller_Router_Exception('No sitemap page is disabled: ' . $id, 404);
        }
        
        
        
        $products = $cmsProductsDbTable->fetchAll($select);
        $this->view->sitemapPage = $sitemapPage;
        $this->view->products = $products;
    }

    public function productAction() {
        
        $request = $this->getRequest();
        
        $id = $request->getParam('id'); //konverzija u integer
        $id = trim($id);
        $id = (int) $id;
        
        //validacija
        if(empty($id)){
           
            throw new Zend_Controller_Router_Exception('No product id', 404);
        }
        
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        
        $select = $cmsProductsDbTable->select();
        $select->where('id = ?', $id)
                ->where('status = ?', Application_Model_DbTable_CmsProducts::STATUS_ENABLED);
        
        $foundProducts = $cmsProductsDbTable->fetchAll($select);
        
        if(count($foundProducts) <=0) {
           
            throw new Zend_Controller_Router_Exception('No product is found for id: ' . $id, 404);
        }
        
        $product = $foundProducts[0];
        
        //pokusamo da dobijemo parametre product slug i onda redirektujemo
//        $productSlug = $request->getParam('product_slug');
//        if (empty($productSlug)){
//            $redirector = $this->getHelper('Redirector');
//            $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'id' => $product['id'],
//                            'product_slug' => $product['first_name'] . '-' . $product['last_name']
//                                ), 'product-route', true);
//        }
        
        //Fetching all other products
        $select = $cmsProductsDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsProducts::STATUS_ENABLED)
                ->where('id != ?', $id)
                ->order('order_number');
        
        $products = $cmsProductsDbTable->fetchAll($select);
        
        $this->view->products = $products;
        
        $this->view->product = $product;
    }

}

