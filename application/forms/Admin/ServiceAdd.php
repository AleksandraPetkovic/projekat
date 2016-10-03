<?php

class Application_Form_Admin_ServiceAdd extends Zend_Form
{
    public function init() {
        
        $firstName = new Zend_Form_Element_Text('service_name');
        //$firstName->addFilter(new Zend_Filter_StringTrim());   isto sto i ovo dole
        //$firstName->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 255)));
        
        $firstName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 3, 'max' => 255)) //false znaci nemoj da prekidas akciju ostalih validatora ako ih ima vise od jednog
                ->setRequired(true); //ispituje da li je prazan string, da li je polje obavezno
        
        $this->addElement($firstName);
        
        
        
        $resume = new Zend_Form_Element_Textarea('resume');
        $resume->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($resume);
        
        
        $servicePhoto = new Zend_Form_Element_File('service_photo');
        $servicePhoto->addValidator('Count', true, 1)
                ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))
                ->addValidator('ImageSize', false, array(
                    'minwidth' => 150,
                    'minheight' => 150,
                    'maxwidth' => 5000,
                    'maxheight' => 5000
                    ))
                ->addValidator('Size', false, array(
                    'max' => '10MB'
                    ))
                //disable move file to destination when calling method getValues
                ->setValueDisabled(true)
                ->setRequired(false);
        
        $this->addElement($servicePhoto);
    }

}

