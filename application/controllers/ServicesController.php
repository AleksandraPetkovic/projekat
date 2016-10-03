<?php
class ServicesController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    public function indexAction()
    {
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        
        $request = $this->getRequest();
        $sitemapPageId = (int) $request->getParam('sitemap_page_id');
        if ($sitemapPageId <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $sitemapPageId, 404);
        }
        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
        $sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        if (!$sitemapPage) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $sitemapPageId, 404);
        }

        $select = $cmsServicesDbTable->select();
        $select->where('status = ?', Application_Model_DbTable_CmsServices::STATUS_ENABLED);
                
       
        
        $services = $cmsServicesDbTable->fetchAll($select);
        $this->view->services = $services;
        $this->view->sitemapPage = $sitemapPage;
    }
}