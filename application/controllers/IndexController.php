<?php
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    public function indexAction()
    {
         $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' =>  $flashMessenger->getMessages('errors'),
        );
        
       
        
        //prikaz  slide-ova
        //
        $cmsSlidesDBTable = new Application_Model_DbTable_CmsIndexSlides ();
       
       $indexSlides = $cmsSlidesDBTable->search(array(
           'filters' => array(
               'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
           ),
           'orders' => array(
                'order_number' => 'ASC',
            )
       ));
       
        
        //prikaz servisa
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
       
       
        $services = $cmsServicesDbTable->search(array(
           'filters' => array(
               'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
           ),
           'orders' => array(
                'order_number' => 'ASC',
            ),
            'limit'=> 4
       ));
        
        //sitemappage
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        $servicesSitemapPages = $cmsSitemapPagesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
				'type' => 'ServicesPage'
			),
                        'limit'=> 1
		));
        $servicesSitemapPages = !empty($servicesSitemapPages) ?$servicesSitemapPages[0] : null;
        //$serviceSitemapPageId = $servicesSitemapPages[0]['id'];
            
        
        
        //photoSitemapPAges
        
        $photoGalleriesPages = $cmsSitemapPagesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED,
				'type' => 'PhotoGalleriesPage'
			),
                        'limit'=> 1
		));
        
        $photoGalleriesPages = !empty($photoGalleriesPages) ? $photoGalleriesPages[0] : null;
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries ();
        $photoGalleries = $cmsPhotoGalleriesTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC',
            ),
            'limit' => 3
        ));
        
        
        $this->view->photoGalleries = $photoGalleries;
        $this->view->photoGalleriesPages = $photoGalleriesPages;
        $this->view->services = $services;
        $this->view->indexSlides = $indexSlides;
        $this->view->systemMessages =  $systemMessages;
        $this->view->servicesSitemapPages =  $servicesSitemapPages;
    }
    
    
    public function testAction()
    {  
        
    }
}