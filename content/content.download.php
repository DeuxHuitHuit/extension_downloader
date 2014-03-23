<?php
	/*
	Copyight: Deux Huit Huit 2014
	License: MIT, see the LICENCE file
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.jsonpage.php');
	require_once(TOOLKIT . '/class.gateway.php');

	class contentExtensionExtension_DownloaderDownload extends JSONPage {

		private $downloadUrl;
		private $extensionHandle;
		
		/**
		 *
		 * Builds the content view
		 */
		public function view() {
			try {
				$this->parseInput();
				$this->download();
				$this->_Result['success'] = true; 
			} catch (Exception $e) {
				$this->_Result['error'] = $e->getMessage();
			}
		}
		
		private function parseInput() {
			$query = General::sanitize($_REQUEST['q']);
			if (empty($query)) {
				throw new Exception(__('Query cannot be empty'));
			} else if (strpos($query, 'zipball') !== FALSE) {
				// full url
				$this->downloadUrl = $query;
			} else if (strpos($query, '/') !== FALSE) {
				// github-user/repo-name
				$explodedQuery = explode('/', $query);
				$this->extensionHandle = $explodedQuery[count($explodedQuery)-1];
				$this->downloadUrl = "https://github.com/$query/zipball/master";
			}
		}
		
		private function download() {
			// create the Gateway object
			$gateway = new Gateway();

			// set our url
			$gateway->init($this->downloadUrl);

			// get the raw response, ignore errors
			$response = @$gateway->exec();
			
			if (!$response) {
				throw new Exception(__("Could not read from $this->tarUrl"));
			}
			
			// write the output
			$tmpFile = MANIFEST . '/tmp/' . Lang::createHandle($this->extensionHandle);
			
			if (!General::writeFile($tmpFile, $response)) {
				throw new Exception(__("Could not write file."));
			}
			
			// open the zip
			$zip = new ZipArchive();
			if (!$zip->open($tmpFile)) {
				General::deleteFile($tmpFile, true); 
				throw new Exception(__("Could not open downloaded file."));
			}
			
			// get the directory name
			$dirname = $zip->getNameIndex(0);
			
			// extract
			$zip->extractTo(EXTENSIONS);
			$zip->close();
			
			// delete tarbal
			General::deleteFile($tmpFile, false); 
			
			// rename extension folder
			if (!@rename(EXTENSIONS . '/' . $dirname, EXTENSIONS . '/' . $this->extensionHandle)) {
				throw new Excpetion(__('Could not rename directory %s', array($dirname)));
			}
		}
	}