<?php
	/*
	Copyight: Deux Huit Huit 2014
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(EXTENSIONS . '/extension_downloader/lib/require.php');
	require_once(EXTENSIONS . '/extension_downloader/lib/class.symphonyextensions.php');
	require_once(TOOLKIT . '/class.gateway.php');

	class contentExtensionExtension_DownloaderDownload extends JSONPage {

		private $forceOverwrite;
		private $alreadyExists;
		private $downloadUrl;
		private $extensionHandle;
		
		private function getDestinationDirectory() {
			return EXTENSIONS . '/' . $this->extensionHandle;	
		}
		
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
				$this->_Result['success'] = false; 
				$this->_Result['error'] = $e->getMessage();
			}
			$this->_Result['handle'] = $this->extensionHandle; 
			$this->_Result['exists'] = $this->alreadyExists;
			$this->_Result['force'] = $this->forceOverwrite;
		}
		
		private static function handleFromPath($path) {
			// github-user/repo-name
			// complete github url
			// custom zip url
			$path = str_replace('/zipball/master', '', $path);
			$parts = explode('/', $path);
			$handle = $parts[count($parts)-1];
			$parts = explode('.', $handle);
			return $parts[count($parts)-1];
		}
		
		private function parseInput() {
			$query = General::sanitize($_REQUEST['q']);
			
			$this->forceOverwrite = (isset($_REQUEST['force']) && General::sanitize($_REQUEST['force']) == 'true');
			
			if (empty($query)) {
				throw new Exception(__('Query cannot be empty'));
			} else if (strpos($query, 'zipball') !== FALSE || strpos($query, '.zip') !== FALSE) {
				// full url
				$this->downloadUrl = $query;
				$this->extensionHandle = self::handleFromPath($query);
			} else if (strpos($query, '/') !== FALSE) {
				$this->extensionHandle = self::handleFromPath($query);
				$this->downloadUrl = "https://github.com/$query/zipball/master";
			} else {
				// do a search for this handle
				$this->searchExtension($query);
			}
			
			// check if directory exists
			$this->alreadyExists = file_exists($this->getDestinationDirectory());
			
			if (!$this->forceOverwrite && $this->alreadyExists) {
				throw new Exception(__('Extension %s already exists', array($this->extensionHandle)));
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
				throw new Exception(__("Could not read from %s", array($this->downloadUrl)));
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
			
			// prepare
			$curDir = EXTENSIONS . '/' . $dirname;
			$toDir = $this->getDestinationDirectory();
			
			// delete current version
			if (!General::deleteDirectory($toDir)) {
				throw new Exception(__('Could not delete %s', array($toDir)));
			}
			
			// rename extension folder
			if (!@rename($curDir, $toDir)) {
				throw new Exception(__('Could not rename %s to %s', array($curDir, $toDir)));
			}
		}
		
		private function searchExtension($query) {
			
			$xml = SymphonyExtensions::getExtensionAsXML($query);
			$xml = current($xml);
			
			$this->extensionHandle = $xml->xpath('/response/extension/@id');
			
			if (empty($this->extensionHandle)) {
				throw new Exception(__("Could not find extension handle"));
			} else {
				$this->extensionHandle = (string)$this->extensionHandle[0];
			}
			
			$this->downloadUrl = $xml->xpath("/response/extension/link[@rel='github:zip']/@href");
			
			if (empty($this->downloadUrl)) {
				throw new Exception(__("Could not find extension handle"));
			} else {
				$this->downloadUrl = (string)$this->downloadUrl[0];
			}
		}
	}