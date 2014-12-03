<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache'] = array();
}
?>