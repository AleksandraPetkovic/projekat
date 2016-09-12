<?php

class Admin_ProductsController extends Zend_Controller_Action {

    public function indexAction() {

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        //prikaz svih product-a
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();


        
        $products = $cmsProductsDbTable->search(array(
            'orders' => array(
                'order_number' => 'ASC'
            )
        ));
            

        $this->view->products = $products;
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        //
        $request = $this->getRequest();

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        $form = new Application_Form_Admin_ProductAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') { //if se izvrsava ako je pokrenuta forma
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) { //ako su svi validatori prosli forma je validna
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new product'); //trazimo gresku bacamo exception
                }

                //get form data
                $formData = $form->getValues();

                //remove key product_photo from form data because there is no column 'product_photo' in cms_products
                unset($formData['product_photo']);

                //Insertujemo novi zapis u tabelu
                $cmsProductsTable = new Application_Model_DbTable_CmsProducts();


                //insert product returns ID of the new product //insertovanje u bazu
                $productId = $cmsProductsTable->insertProduct($formData);

                //da li je uloadovano, provera
                if ($form->getElement('product_photo')->isUploaded()) {
                    //photo is uploaded

                    $fileInfos = $form->getElement('product_photo')->getFileInfo('product_photo');
                    $fileInfo = $fileInfos['product_photo'];
                    //isto kao prethodne dve linije gore
                    //$fileInfos = $_FILES['product_photo']

                    try {
                        //make je putanja do slike
                        //open uploaded photo in temporary directory
                        $productPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $productPhoto->fit(150, 150);

                        //kakvu god ekstenziju da ubacimo on je konvertuje u jpg// 
                        $productPhoto->save(PUBLIC_PATH . '/uploads/products/' . $productId . '.jpg');
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Product has beeen saved but error occured during image processing', 'success');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_products',
                                    //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                                    'action' => 'index',
                                    'id' => $productId
                                        ), 'default', true);
                    }
                }


                //set system message
                //ovde redjamo sistemske poruke
                $flashMessenger->addMessage('Product has beeen saved', 'success');
               
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_products',
                            //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) { //hvatamo gresku i ispisujemo je :)
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }

    public function editAction() {

        $request = $this->getRequest();

        //(int) sve sto nije integer pretvara u nulu :)
        $id = (int) $request->getParam('id');

        if ($id <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id, 404);
        }

        $cmsProductsTable = new Application_Model_DbTable_CmsProducts();

        $product = $cmsProductsTable->getProductById($id);

        if (empty($product)) {
            throw new Zend_Controller_Router_Exception('No product is found with id: ' . $id, 404);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        //forma sluzi za filtriranje i validaciju polja
        $form = new Application_Form_Admin_ProductAdd();

        //default form data
        $form->populate($product);



        if ($request->isPost() && $request->getPost('task') === 'update') { //if se izvrsava ako je pokrenuta forma
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) { //ako su svi validatori prosli forma je validna
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for product'); //trazimo gresku bacamo exception
                }

                //get form data
                $formData = $form->getValues();
                unset($formData['product_photo']);

                if ($form->getElement('product_photo')->isUploaded()) {
                    //photo is uploaded

                    $fileInfos = $form->getElement('product_photo')->getFileInfo('product_photo');
                    $fileInfo = $fileInfos['product_photo'];
                    //isto kao prethodne dve linije gore
                    //$fileInfos = $_FILES['product_photo']

                    try {
                        //make je putanja do slike
                        //open uploaded photo in temporary directory
                        $productPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $productPhoto->fit(150, 150);

                        //kakvu god ekstenziju da ubacimo on je konvertuje u jpg// 
                        $productPhoto->save(PUBLIC_PATH . '/front/products/' . $product['id'] . '.jpg');
                    } catch (Exception $ex) {
                        //ne redirektujemo na neku drugu stranu nego ostajemo na toj strani 
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                    }
                }




                //Radimo update postojeceg zapisa u tabeli
                $cmsProductsTable->updateProduct($product['id'], $formData);



                // do actual task
                //save to database etc
                //set system message
                //ovde redjamo sistemske poruke
                $flashMessenger->addMessage('Product has beeen updated', 'success');
                //ovo su primera dva dole da vidimo kako izgleda
                //$flashMessenger->addMessage('Or not maybe something is wrong', 'errors');
                //$flashMessenger->addMessage('success message 2', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_products',
                            //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) { //hvatamo gresku i ispisujemo je :)
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;


        $this->view->product = $product;
    }

    public function deleteAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->isPost('task') != 'delete') {
            //request is not post redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {
            //(int) sve sto nije integer pretvara u nulu :)
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {


                //prekida se izvrsavanje programa i prikazuje se "Page not found"
                throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id);
            }

            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();

            $product = $cmsProductsTable->getProductById($id);


            if (empty($product)) {

                throw new Zend_Controller_Router_Exception('No product is found with id: ' . $id, 'errors');
            }

            $cmsProductsTable->deleteProduct($id);


            $flashMessenger->addMessage('Product ' . $product['first_name'] . ' ' . $product['last_name'] . 'has been deleted', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function disableAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->isPost('task') != 'disable') {
            //request is not post redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {
            //(int) sve sto nije integer pretvara u nulu :)
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {


                //prekida se izvrsavanje programa i prikazuje se "Page not found"
                throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id);
            }

            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();

            $product = $cmsProductsTable->getProductById($id);


            if (empty($product)) {

                throw new Zend_Controller_Router_Exception('No product is found with id: ' . $id, 'errors');
            }

            $cmsProductsTable->disableProduct($id);


            $flashMessenger->addMessage('Product ' . $product['first_name'] . ' ' . $product['last_name'] . 'has been disabled', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function enableAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->isPost('task') != 'enable') {
            //request is not post redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {
            //(int) sve sto nije integer pretvara u nulu :)
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {


                //prekida se izvrsavanje programa i prikazuje se "Page not found"
                throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id);
            }

            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();

            $product = $cmsProductsTable->getProductById($id);


            if (empty($product)) {

                throw new Zend_Controller_Router_Exception('No product is found with id: ' . $id, 'errors');
            }

            $cmsProductsTable->enableProduct($id);


            $flashMessenger->addMessage('Product ' . $product['first_name'] . ' ' . $product['last_name'] . 'has been enabled', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function updateorderAction() {
        $request = $this->getRequest();

        if (!$request->isPost() || $request->isPost('task') != 'saveOrder') {
            //request is not post redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {

            $sortedIds = $request->getPost('sorted_ids');

            if (empty($sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }

            //trimujemo po spejsu i po zarezu
            $sortedIds = trim($sortedIds, ' ,');

            //proveravamo da li je od nula do devet i zarez i mora da ima vise od jednog karaktera
            //zvezda znaci da ono sto je u zagradi da moze vise puta da se nadje
            if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }

            $sortedIds = explode(',', $sortedIds);

            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();

            $cmsProductsTable->updateOrderOfProducts($sortedIds);

            $flashMessenger->addMessage('Order is successfully saved', 'success');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function dashboardAction(){
        
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        
        $countOfEnabledProducts = $cmsProductsDbTable->count(array(
            'status' => Application_Model_DbTable_CmsProducts::STATUS_ENABLED,
        ));
        
        $countAllProducts = $cmsProductsDbTable->count();
    
        $this->view->countOfEnabledProducts = $countOfEnabledProducts;
        $this->view->countAllProducts = $countAllProducts;
    }
    
    
}
