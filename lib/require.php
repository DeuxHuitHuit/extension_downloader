<?php
	/*
	Copyight: Deux Huit Huit 2014
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
	
	// Only for 2.4+
	if (file_exists(TOOLKIT . '/class.jsonpage.php')) {
		require_once(TOOLKIT . '/class.jsonpage.php');
		
	}
	// 2.3-
	else {
		
		abstract class JSONPage extends AjaxPage {
			
			public function __construct() {
				parent::__construct();
				$this->_Result = array();
			}
			
			public function handleFailedAuthorisation(){
				$this->setHttpStatus(self::HTTP_STATUS_UNAUTHORIZED);
				$this->_Result = json_encode(array('status' => __('You are not authorized to access this page.')));
			}
			
			public function generate($page = null){
				header('Content-Type: application/json');
				// Set the actual status code in the xml response
				$this->_Result['status'] = $this->getHttpStatusCode();
				echo json_encode($this->_Result);
				exit;
			}
			
		}
		
	}