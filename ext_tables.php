<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if(version_compare(TYPO3_branch, '6.2', '<') && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bgm_hreflang')){
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('bgm_hreflang') . '/Configuration/TCA/Overrides/pages.php');
}

?>