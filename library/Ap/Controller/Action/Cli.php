<?php

class Ap_Controller_Action_Cli extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		if (PHP_SAPI != 'cli') {
			throw new Exception('only cli usage');
		}
		//tmp dir. move to CONSTANTS in app?
		$this->_tmp_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR
			. 'data' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
		//crunch for temporary
		$writer = new Zend_Log_Writer_Stream('php://output');
		$logger = new Zend_Log($writer);
		Zend_Registry::set('Log', $logger);

		$stream = $this->getRequest()->getParam('stream', 0);
		if(!empty($stream)){
			$lock_fname = $this->_tmp_dir . $this->getRequest()->getParam('action') . '.lck';
			$locker = new Ap_Application_Lock();
			if(!$locker->lock($lock_fname)){
				echo "Already running? {$this->getRequest()->getParam('action')}\n";
				exit(0);
			}
		}
	}

	public function goFpmAction() {
		$path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
		$files_action = Ap_Search_Finder::type('file')
			->name('*.go')->in( $path );
		foreach ($files_action as $key => $file) {
			$action = pathinfo($file, PATHINFO_FILENAME) . "Action";
			if (method_exists($this, $action)) {
				try {
					$this->$action();
				} catch (Exception $exc) {
					$message  = $exc->getMessage().PHP_EOL;
					$message .= $exc->getTraceAsString().PHP_EOL;
					$mail = new Zend_Mail('UTF-8');
					$mail->addTo(Zend_Registry::get('Config')->logging->adminMail)
						 ->setSubject('goFPM error '.Zend_Registry::get('Config')->maindomain)
						 ->setBodyText($message);
					$mail->send();
				}
				unlink($file);
				break;
			}
		}
	}
}