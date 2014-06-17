<?php
	/*
	Copyight: Deux Huit Huit 2014
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(EXTENSIONS . '/extension_downloader/lib/require.php');
	require_once(EXTENSIONS . '/extension_downloader/lib/class.symphonyextensions.php');
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
			
			$this->version = Symphony::Configuration()->get('version', 'symphony');
			
			if (!isset($_REQUEST['compatible']) || $_REQUEST['compatible'] == 'true') {
				$this->compatibleVersion = $this->version;
			}
		}
		
		private function search() {
			$results = array();
			$url = "http://symphonyextensions.com/api/extensions/?keywords=$this->query&type=&compatible-with=$this->compatibleVersion&sort=updated&order=desc";
			
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
				$developer = $ext->xpath('developer/name');
				$version = $ext->xpath('version');
				$status = $ext->xpath('status');
				$compatible = $ext->xpath("compatibility/symphony[@version='$this->version']");
				
				$res = array(
					'handle' => (string)$id[0],
					'name' => (string)$name[0],
					'by' => (string)$developer[0],
					'version' => (string)$version[0],
					'status' => (string)$status[0],
					'compatible' => ($compatible != null),
				);
				
				$results[] = $res;
			}
			
			// set results array
			$this->_Result['results'] = $results;
		}
		
	}