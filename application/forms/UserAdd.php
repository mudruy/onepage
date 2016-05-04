<?php

class Application_Form_UserAdd extends Zend_Form {

    protected $_object;

    public function getObject() {
        return $this->_object;
    }

    function __construct() {
        $table = new Application_Model_User();
        $this->_object = $table->createRow();
        $this->init();
        $this->loadDefaultDecorators();
    }

    public function init() {

        $this->addElement('text', 'name');
        $this->name->setRequired(true)
                ->addValidator('NotEmpty', true)
                ->addValidator(new Zend_Validate_StringLength(array('max' => 255)))
                ->getValidator('NotEmpty')
                ->setMessage('User_Auth::Name is empty', 'isEmpty');




        $email_validator = new Zend_Validate_Db_NoRecordExists($this->getObject()->getTable()->info('name'), 'login');
        $email_validator->setMessage('User_Auth::Е-mail is used', Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND);

        $this->addElement('text', 'login');
        $this->login->setRequired(true)
                ->addFilter('StringToLower')
                ->addValidator('NotEmpty', true)
                ->addValidator(new Zend_Validate_StringLength(array('max' => 255)))
                ->addValidator(new Zend_Validate_EmailAddress(), true)
                ->addValidator($email_validator)
                ->getValidator('NotEmpty')
                ->setMessage('User_Auth::E-mail is empty', 'isEmpty');

        $this->addElement('password', 'password');
        $this->password->setRequired(true)
                ->setAttrib('autocomplete', 'off')
                ->addValidator(new Zend_Validate_StringLength(array('max' => 24, 'min' => 6)))
                ->addValidator('NotEmpty')
                ->getValidator('NotEmpty')
                ->setMessage('User_Auth::Password is empty', 'isEmpty');

        $this->addElement('password', 'password_again');
        $this->password_again->setRequired(true)
                ->setAttrib('autocomplete', 'off')
                ->addValidator('NotEmpty', true)
                ->addValidator(new Ap_Validate_Password)
                ->getValidator('NotEmpty')
                ->setMessage('Input is empty', 'isEmpty');
        
        $this->addElement('submit', 'add', array('label' => 'Add'));
    }

    public function isValid($data) {
        if (parent::isValid($data)) {
            $this->getObject()
                    ->setFromArray(array(
                        'name' => $this->name->getValue(),
                        'login' => $this->login->getValue(),
            ));
            $this->getObject()->setPassword($this->password->getValue());
            return true;
        }
        return false;
    }

}
