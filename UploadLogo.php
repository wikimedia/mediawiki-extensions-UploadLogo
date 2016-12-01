<?php

/***********************************************************
 * Name:     UploadLogo
 * Desc:     Logo Manage By Uploaded File
 *
 * Version:  1.0.0
 *
 * Author:   sleepinglion
 * Homepage: https://www.mediawiki.org/wiki/Extension:UploadLogo
 * 			 https://github.com/sleepinglion
 *           http://www.sleepinglion.pe.kr
 * 			 
 *
 * License:  MIT
 *
 ***********************************************************
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'UploadLogo' );
	/*
	# Tell MediaWiki about the new special page and its class name
	/* wfWarn(
	 'Deprecated PHP entry point used for WikiEditor extension. Please use wfLoadExtension instead, ' .
	 'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	 );*/
	
	return true;
} else {
	//die( 'This version of the Nuke extension requires MediaWiki 1.25+' );
	
	$wgExtensionCredits['specialpage'][] = array(
		'path' => __FILE__,
		'name' => 'UploadLogo',
		'author' => 'sleepinglion',
		'url' => 'https://www.mediawiki.org/wiki/Extension:UploadLogo',
		'descriptionmsg' => 'Logo Manage By Uploaded File',
		'version' => '1.0'
	);

	$wgAutoloadClasses['SpecialUploadLogo'] = __DIR__ . '/SpecialUploadLogo.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
	$wgMessagesDirs['UploadLogo'] = __DIR__ . "/i18n"; # Location of localisation files (Tell MediaWiki to load them)
	$wgExtensionMessagesFiles['UploadLogoAlias'] = __DIR__ . '/UploadLogo.alias.php'; # Location of an aliases file (Tell MediaWiki to load it)
	$wgSpecialPages['UploadLogo'] = 'SpecialUploadLogo'; # Tell MediaWiki about the new special page and its class name
}
