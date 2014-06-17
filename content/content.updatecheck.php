<?php
	/*
	Copyight: Deux Huit Huit 2014
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(EXTENSIONS . '/extension_downloader/lib/require.php');
	require_once(EXTENSIONS . '/extension_downloader/lib/class.symphonyextensions.php');
	require_once(TOOLKIT . '/class.gateway.php');

	class contentExtensionExtension_DownloaderUpdatecheck extends JSONPage {

		private $extensionHandle;
		private $baseVersion;
		
		/**
		 *
		 * Builds the content view
		 */
		public function view() {
			try {
				$this->parseInput();
				$this->check();
				$this->_Result['success'] = true;
			} catch (Exception $e) {
				$this->_Result['success'] = false; 
				$this->_Result['error'] = $e->getMessage();
			}
			$this->_Result['handle'] = $this->extensionHandle; 
			$this->_Result['update'] = false;
			$this->_Result['version'] = '';
		}
		
		private function parseInput() {
			$this->extensionHandle = General::sanitize($_REQUEST['handle']);
			
			$this->baseVersion = General::sanitize($_REQUEST['version']);
			
			if (empty($this->extensionHandle)) {
				throw new Exception(__('Handle cannot be empty'));
			} else if (empty($this->baseVersion)) {
				throw new Exception(__('Version cannot be empty'));
			}
		}
		
		private function check() {
			$ext = SymphonyExtensions::getExtensionAsXML($this->extensionHandle);
			
			// $xml->xpath('/response/extension/@id');
		}
	}