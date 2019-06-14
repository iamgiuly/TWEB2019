<?php

class AdminController extends Zend_Controller_Action
{
    protected $_database;
    protected $_redirector;
    
    protected $_adminModel;
    protected $_form;

    public function init() {
        $this->_database = Application_Model_DBContext::Instance();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        if(!$this->view->acl->isAllowed($this->view->currentRole, null, 'Admin')){
            $this->_redirector->gotoSimple('auth', 'error');    
        }

        $this->view->headScript()->appendFile($this->view->baseUrl('js/messanger.js'));
        $this->view->layout = 'admin';
        
        
        
        
        $this->_adminModel = new Application_Model_Admin();
        
    }
    
    public function indexAction() {
        $this->view->mesi = array(null, 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');
        $this->view->prospettoMese = $this->_database->getProspettoMensile();
        $this->view->prospettoAnno = $this->_database->getProspettoANno();
    }
    
    
    public function aboutusAction(){ $this->_helper->viewRenderer->renderBySpec('aboutus', array('controller' => 'public')); }
    public function contactsAction(){ $this->_helper->viewRenderer->renderBySpec('contacts', array('controller' => 'public')); }
    public function rulesAction(){ $this->_helper->viewRenderer->renderBySpec('rules', array('controller' => 'public')); }
    public function faqAction(){
        $this->view->assign(array('allFaqs' => $this->_database->getFaqs()));
    }
    

    public function newfaqAction(){
        $this->view->faqForm = $this->getFaqForm();
    }
   
    public function addfaqAction()
	{
        $this->getFaqForm();
		if (!$this->getRequest()->isPost()) {
			$this->_redirector->goToSimple('faq', 'admin');
		}
		$form=$this->_form;
		if (!$form->isValid($_POST)) {
			return $this->render('newfaq');
		}
		$values = $form->getValues();
		$this->_adminModel->saveFaq($values);
        $this->_redirector->goToSimple('faq', 'admin');
	}
    private function getFaqForm()
	{
		$urlHelper = $this->_helper->getHelper('url');
		$this->_form = new Application_Form_Admin_Faq_Add();
		$this->_form->setAction($urlHelper->url(array(
				'controller' => 'admin',
				'action' => 'addfaq'),
				'default'
				));
		return $this->_form;
	}
        
    
    public function editfaqAction(){
        $faqID = intval($this->_getParam('ID', 0));
        $faq = $this->_database->getFaqById($faqID);

        if($faq == null){ $this->view->error = 'Faq non trovata'; }
        else{
            
            $this->view->faq = $faq;
            $editForm2 = new App_Form_FaqEdit($faq);

            if(count($_POST) > 0 && $editForm2->isValid($_POST)){
                $values = $editForm2->getValues();
                $values['ID'] = $faq->ID;
                $this->_database->updateFaq($values);
                $this->_redirector->goToSimple('faq', 'admin');
            }

            $this->view->editForm2 = $editForm2;
        }

    }    
        
        
        
    public function deletefaqAction(){
        $faqid = intval($this->_getParam('id', 0));
        $faq = $this->_database->getFaqById($faqid);

        if($faq == null){ $this->view->error = 'Faq non trovata'; }
        else{
            $this->_database->deleteFaq($faq['ID']);
            $this->_redirector->goToSimple('faq', 'admin');
        }
    }
    public function profileAction(){
        $profileForm = new App_Form_Profile($this->view->user);
        if(count($_POST) > 0 && $profileForm->isValid($_POST)){
            $values = $profileForm->getValues();
            $update = array();
            $update['ID'] = $this->view->user->ID;
            $update['Email'] = $values['email'];
            $this->view->user->Email = $values['email'];
            if($values['password']){
                $update['Password'] = $values['password'];
                $this->view->user->Password = $values['Password'];
            }
            $this->_database->updateUser($update);

            $this->view->success = 'Modifiche apportate con successo!';
        }
        $this->view->profileForm = $profileForm;
        $this->_helper->viewRenderer->renderBySpec('profile', array('controller' => 'user'));
    }

    public function catalogAction(){
        $paged = $this->_getParam('page', 1);
        $ordinator=$this->_getParam('orderBy',null);

        $form = new App_Form_Catalogfilter();
        
        if (!$form->isValid($_POST)) { return $this->render('catalog'); }
        
        $values = $form->getValues();
        
        $this->view->assign(array(
            'catalog' => $this->_database->getCatalog($values, $ordinator, $paged),
            'catalogForm' => $form,
            'bottoneModifica' => '<input type="button" class="btn btn-primary" value="MODIFICA" style="font-size: 2em">',
            'bottoneElimina' => '<input type="button" class="btn btn-danger" value="ELIMINA" style="font-size: 2em">',
            'pannelloNoleggio' => '<input type="button" class="btn btn-success" value="AGGIUNGI" style="font-size: 2em">'
        ));
        $this->_helper->viewRenderer->renderBySpec('catalog', array('controller' => 'public'));
    }
    

    public function usersAction(){
        $this->view->users = $this->_database->getAllUsers()->toArray();
        $roles = $this->_database->getRoles()->toArray();
        $works = $this->_database->getOccupazioni()->toArray();

        for($i = 0; $i < count($this->view->users); $i++){
            $this->view->users[$i]['RuoloTesto'] = $roles[$this->view->users[$i]['Ruolo'] - 1]['Nome'];
            $this->view->users[$i]['OccupazioneTesto'] = $works[$this->view->users[$i]['Occupazione'] - 1]['Nome'];
        }
    }

    public function edituserAction(){
        $userid = intval($this->_getParam('id', 0));
        $user = $this->_database->getUserById($userid);

        if($user == null){ $this->view->error = 'Utente non trovato'; }
        else{
            $occ = $this->_database->getOccupazioni();
            $occupazioni = array();
            foreach($occ as $o){ $occupazioni[$o->ID] = $o->Nome; }
            $this->view->user = $user;
            $editForm = new App_Form_UserEdit($occupazioni, $user);

            if(count($_POST) > 0 && $editForm->isValid($_POST)){
                $values = $editForm->getValues();
                
                $values['nascita'] = preg_replace('/(\d\d)[-\/](\d\d)[-\/](\d\d\d\d)/', '$3-$2-$1', $values['nascita']);
                $values['ID'] = $user->ID;
                $this->_database->updateUser($values);
                $this->_redirector->goToSimple('users', 'admin');
            }

            $this->view->editForm = $editForm;
        }

    }

    public function deleteuserAction(){
        $userid = intval($this->_getParam('id', 0));
        $user = $this->_database->getUserById($userid);

        if($user == null){ $this->view->error = 'Utente non trovato'; }
        else{
            $this->_database->deleteUser($user['ID']);
            $this->_redirector->goToSimple('users', 'admin');
        }
    }

}