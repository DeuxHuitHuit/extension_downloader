<?php
	/*
	Copyight: Deux Huit Huit 2014
	License: MIT, see the LICENCE file
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.jsonpage.php');
	require_once(TOOLKIT . '/class.gateway.php');

	class contentExtensionExtension_DownloaderSearch extends JSONPage {

		private $query;
		private $empty;
		private $version;
		
		/**
		 *
		 * Builds the content view
		 */
		public function view() {
			try {
				$this->parseInput();
				$this->search();
				$this->_Result['success'] = true; 
			} catch (Exception $e) {
				$this->_Result['empty'] = $this->empty;
				$this->_Result['success'] = false; 
				$this->_Result['error'] = $e->getMessage();
			}
		}
		
		private function parseInput() {
			$query = General::sanitize($_REQUEST['q']);
			$this->empty = empty($query);
			if ($this->empty) {
				throw new Exception(__('Query cannot be empty'));
			} else {
				// do a search for this query
				$this->query = $query;
			}
			
			if (!isset($_REQUEST['compatible']) || $_REQUEST['compatible'] == 'true') {
				$this->version = Symphony::Configuration()->get('version', 'symphony');
			}
		}
		
		private function search() {
			$results = array();
			$url = "http://symphonyextensions.com/api/extensions/?keywords=$this->query&type=&compatible-with=$this->version&sort=updated&order=desc";
			
			// create the Gateway object
			$gateway = new Gateway();

			// set our url
			$gateway->init($url);

			// get the raw response, ignore errors
			$response = @$gateway->exec();
			
			if (!$response) {
				throw new Exception(__("Could not read from %s", array($url)));
			}
			
			// parse xml
			$xml = @simplexml_load_string($response);
			
			if (!$xml) {
				throw new Exception(__("Could not parse xml from %s", array($url)));
			}
			
			$extensions = $xml->xpath('/response/extensions/extension');
			
			foreach ($extensions as $index => $ext) {
				$name = $ext->xpath('name');
				$id = $ext->xpath('@id');
				
				$res = array(
					'handle' => (string)$id[0],
					'name' => (string)$name[0]
				);
				
				$results[] = $res;
			}
			
			// set results array
			$this->_Result['results'] = $results;
		}
		
	}