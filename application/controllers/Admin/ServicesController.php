<?php

class Admin_ServicesController extends Zend_Controller_Action {

    public function indexAction() {

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        //prikaz svih service-a
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();


        
        $services = $cmsServicesDbTable->search(array(
            'orders' => array(
                'order_number' => 'ASC'
            )
        ));
            

        $this->view->services = $services;
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

        $form = new Application_Form_Admin_ServiceAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') { //if se izvrsava ako je pokrenuta forma
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) { //ako su svi validatori prosli forma je validna
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new service'); //trazimo gresku bacamo exception
                }

                //get form data
                $formData = $form->getValues();

                //remove key service_photo from form data because there is no column 'service_photo' in cms_services
                unset($formData['service_photo']);

                //Insertujemo novi zapis u tabelu
                $cmsServicesTable = new Application_Model_DbTable_CmsServices();


                //insert service returns ID of the new service //insertovanje u bazu
                $serviceId = $cmsServicesTable->insertService($formData);

                //da li je uloadovano, provera
                if ($form->getElement('service_photo')->isUploaded()) {
                    //photo is uploaded

                    $fileInfos = $form->getElement('service_photo')->getFileInfo('service_photo');
                    $fileInfo = $fileInfos['service_photo'];
                    //isto kao prethodne dve linije gore
                    //$fileInfos = $_FILES['service_photo']

                    try {
                        //make je putanja do slike
                        //open uploaded photo in temporary directory
                        $servicePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $servicePhoto->fit(150, 150);

                        //kakvu god ekstenziju da ubacimo on je konvertuje u jpg// 
                        $servicePhoto->save(PUBLIC_PATH . '/uploads/service-photos/' . $serviceId . '.jpg');
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Service has beeen saved but error occured during image processing', 'success');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_services',
                                    //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                                    'action' => 'index',
                                    'id' => $serviceId
                                        ), 'default', true);
                    }
                }


                //set system message
                //ovde redjamo sistemske poruke
                $flashMessenger->addMessage('Service has beeen saved', 'success');
               
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_services',
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
            throw new Zend_Controller_Router_Exception('Invalid service id: ' . $id, 404);
        }

        $cmsServicesTable = new Application_Model_DbTable_CmsServices();

        $service = $cmsServicesTable->getServiceById($id);

        if (empty($service)) {
            throw new Zend_Controller_Router_Exception('No service is found with id: ' . $id, 404);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        //forma sluzi za filtriranje i validaciju polja
        $form = new Application_Form_Admin_ServiceAdd();

        //default form data
        $form->populate($service);



        if ($request->isPost() && $request->getPost('task') === 'update') { //if se izvrsava ako je pokrenuta forma
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) { //ako su svi validatori prosli forma je validna
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for service'); //trazimo gresku bacamo exception
                }

                //get form data
                $formData = $form->getValues();
                unset($formData['service_photo']);

                if ($form->getElement('service_photo')->isUploaded()) {
                    //photo is uploaded

                    $fileInfos = $form->getElement('service_photo')->getFileInfo('service_photo');
                    $fileInfo = $fileInfos['service_photo'];
                    //isto kao prethodne dve linije gore
                    //$fileInfos = $_FILES['service_photo']

                    try {
                        //make je putanja do slike
                        //open uploaded photo in temporary directory
                        $servicePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        $servicePhoto->fit(150, 150);

                        //kakvu god ekstenziju da ubacimo on je konvertuje u jpg// 
                        $servicePhoto->save(PUBLIC_PATH . '/uploads/service-photos/' . $id . '.jpg');
                    } catch (Exception $ex) {
                        //ne redirektujemo na neku drugu stranu nego ostajemo na toj strani 
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                    }
                }




                //Radimo update postojeceg zapisa u tabeli
                $cmsServicesTable->updateService($service['id'], $formData);



                // do actual task
                //save to database etc
                //set system message
                //ovde redjamo sistemske poruke
                $flashMessenger->addMessage('Service has beeen updated', 'success');
                //ovo su primera dva dole da vidimo kako izgleda
                //$flashMessenger->addMessage('Or not maybe something is wrong', 'errors');
                //$flashMessenger->addMessage('success message 2', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_services',
                            //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) { //hvatamo gresku i ispisujemo je :)
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;


        $this->view->service = $service;
    }

    public function deleteAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->isPost('task') != 'delete') {
            //request is not post redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
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
                throw new Zend_Controller_Router_Exception('Invalid service id: ' . $id);
            }

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $service = $cmsServicesTable->getServiceById($id);


            if (empty($service)) {

                throw new Zend_Controller_Router_Exception('No service is found with id: ' . $id, 'errors');
            }

            $cmsServicesTable->deleteService($id);


            $flashMessenger->addMessage('Service ' . $service['first_name'] . ' ' . $service['last_name'] . 'has been deleted', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
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
                        'controller' => 'admin_services',
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
                throw new Zend_Controller_Router_Exception('Invalid service id: ' . $id);
            }

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $service = $cmsServicesTable->getServiceById($id);


            if (empty($service)) {

                throw new Zend_Controller_Router_Exception('No service is found with id: ' . $id, 'errors');
            }

            $cmsServicesTable->disableService($id);


            $flashMessenger->addMessage('Service ' . $service['first_name'] . ' ' . $service['last_name'] . 'has been disabled', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
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
                        'controller' => 'admin_services',
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
                throw new Zend_Controller_Router_Exception('Invalid service id: ' . $id);
            }

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $service = $cmsServicesTable->getServiceById($id);


            if (empty($service)) {

                throw new Zend_Controller_Router_Exception('No service is found with id: ' . $id, 'errors');
            }

            $cmsServicesTable->enableService($id);


            $flashMessenger->addMessage('Service ' . $service['first_name'] . ' ' . $service['last_name'] . 'has been enabled', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
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
                        'controller' => 'admin_services',
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

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $cmsServicesTable->updateOrderOfServices($sortedIds);

            $flashMessenger->addMessage('Order is successfully saved', 'success');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        //ako se ne stavi action onda se podrazumeva index, ovo je stavljeno radi jasnoce :)
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function dashboardAction(){
        
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        
        $countOfEnabledServices = $cmsServicesDbTable->count(array(
            'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED,
        ));
        
        $countAllServices = $cmsServicesDbTable->count();
    
        $this->view->countOfEnabledServices = $countOfEnabledServices;
        $this->view->countAllServices = $countAllServices;
    }
    
    
}
