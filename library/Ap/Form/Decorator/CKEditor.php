<?php

class Ap_Form_Decorator_CKEditor extends Zend_Form_Decorator_Abstract
{
	public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        $view->headScript()
            ->prependFile('/js/ckeditor/adapters/jquery.js')
            ->prependFile('/js/ckeditor/ckeditor.js');
        if (null === $view)
            return $content;
        $renderedContent = $view->partial('CKEditor.phtml',array('element'=>$element));
        return $renderedContent .  $content;
    }
}

