<?php
	/*
	Copyight: Deux Huit Huit 2014
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");


	/**
	 *
	 * @author Deux Huit Huit
	 * http://www.deuxhuithuit.com
	 *
	 */
	class extension_extension_downloader extends Extension {

		/**
		 * Name of the extension
		 * @var string
		 */
		const EXT_NAME = 'Extension Downloader';
		
		/**
		 * Symphony utility function that permits to
		 * implement the Observer/Observable pattern.
		 * We register here delegate that will be fired by Symphony
		 */

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendAssets'
				)
			);
		}

		/**
		 *
		 * Appends javascript file references into the head, if needed
		 * @param array $context
		 */
		public function appendAssets(array $context) {
			// store the callback array locally
			$c = Administration::instance()->getPageCallback();
			
			// extension page
			if($c['driver'] == 'systemextensions') {

				Administration::instance()->Page->addStylesheetToHead(
					URL . '/extensions/extension_downloader/assets/extension_downloader.css',
					'screen',
					time() + 1,
					false
				);
				Administration::instance()->Page->addScriptToHead(
					URL . '/extensions/extension_downloader/assets/extension_downloader.js',
					time(),
					false
				);

				return;
			}
		}

		/* ********* INSTALL/UPDATE/UNINSTALL ******* */

		/**
		 * Creates the table needed for the settings of the field
		 */
		public function install() {
			return true;

		}

		/**
		 * Creates the table needed for the settings of the field
		 */
		public function update($previousVersion = false) {
			return true;
		}

		/**
		 *
		 * Drops the table needed for the settings of the field
		 */
		public function uninstall() {
			return true;
		}

	}