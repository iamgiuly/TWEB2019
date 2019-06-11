<?php
class Application_Form_Public_Catalog_Filter extends Zend_Form
{
    protected $_publicModel;

	public function init()
        {
            $this->_publicModel = new Application_Model_Public();
		$this->setMethod('post');
		$this->setName('filtercar');
		$this->setAction('');
                
                
            $this->addElement('text', 'modello', array(
            'label' => 'Modello',
            'filters' => array('StringTrim'),
            'required' => true,
            'description' => 'Modello della macchina',
            'validators' => array(array('StringLength',true, array(1,25))),
		));          
            
            $this->addElement('text', 'marca', array(
            'label' => 'Marca',
            'filters' => array('StringTrim'),
            'required' => true,
            'description' => 'Marca della macchina',
            'validators' => array(array('StringLength',true, array(1,25))),
		)); 
            
            $this->addElement('text', 'prezzoMin', array(
            'label' => 'Prezzo Minimo',
            'required' => true,
            'filters' => array('LocalizedToNormalized'),
            'validators' => array(array('Float', true, array('locale' => 'en_US'))),
		));
            
            $this->addElement('text', 'prezzoMax', array(
            'label' => 'Prezzo Massimo',
            'required' => true,
            'filters' => array('LocalizedToNormalized'),
            'validators' => array(array('Float', true, array('locale' => 'en_US'))),
		));
            
            $this->addElement('text', 'posti', array(
            'label' => 'Posti',
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(array('StringLength',true, array(1,25))),
		));
          
            
            
            
        
            
            
            $this->addElement('submit', 'search', array(
            'label' => 'RICERCA',
		));
            
        }
    
    
}