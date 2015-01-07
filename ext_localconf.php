<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache'] = array();
}

/* DEMO CONFIGURATION
$languageMapping = array(
	//"sys_language_uid" and "isolanguagecode" have to be unique in the array languageMapping!
	sys_language_uid => isolanguagecode,
	1 => 'de', //Deutsch
	2 => 'en', //Englisch
	3 => 'fr', //Französisch
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'] = array(
	//rootpage of a country tree; "pageid" has to be unique in the array countryMapping!
	pageid => array(
		//"isocountrycode" has to be unique as countryCode in the array countryMapping!
		'countryCode' => isocountrycode,
		//"$languageMapping + array(0 => isolanguagecode)" can be assigned more than once with the same isolanguagecode as languageMapping in the array countryMapping.
		'languageMapping' => $languageMapping + array(0 => isolanguagecode),
	),
	12 => array( //International
		'countryCode' => 'en',
		'languageMapping' => $languageMapping + array(0 => 'en'),
	),
	34 => array( //Deutschland
		'countryCode' => 'de',
		'languageMapping' => $languageMapping + array(0 => 'de'),
	),
	56 => array( //UK
		'countryCode' => 'gb',
		'languageMapping' => $languageMapping + array(0 => 'en'),
	),
	78 => array( //France
		'countryCode' => 'fr',
		'languageMapping' => $languageMapping + array(0 => 'fr'),
	),
);
//If L==0, pages in this tree are rendered with 'x-default', else only the isolanguagecode is used (without the isocountrycode)
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'] = 12;
*/
?>