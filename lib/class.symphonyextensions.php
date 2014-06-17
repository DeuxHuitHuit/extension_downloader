<?php
	/*
	Copyight: Deux Huit Huit 2014
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
		
	class SymphonyExtensions {
		
		const URL_ROOT = 'http://symphonyextensions.com/api/';
		
		public static function getExtensionAsXML($handle) {
			$url = self::URL_ROOT . "extensions/{$handle}/";
			
			// create the Gateway object
			$gateway = new Gateway();

			// set our url
			$gateway->init($url);

			// get the raw response, ignore errors
			$response = @$gateway->exec();
			
			if (!$response) {
				throw new Exception(__("Could not read from %s", array($this->downloadUrl)));
			}
			
			// parse xml
			$xml = @simplexml_load_string($response);
			
			if (!$xml) {
				throw new Exception(__("Could not parse xml from %s", array($url)));
			}
			
			$extension = $xml->xpath('/response/extension');
			
			if (empty($extension)) {
				throw new Exception(__("Could not find extension %s", array($query)));
			}
			
			return $extension;
		}
		
		
	}