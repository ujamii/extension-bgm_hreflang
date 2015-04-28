<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array(
	'tx_bgmhreflang_1' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:bgm_hreflang/Resources/Private/Language/Backend.xlf:pages.tx_bgmhreflang_1',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'pages',
			'foreign_table' => 'pages',
			'MM' => 'tx_bgmhreflang_page_page_mm',
			'MM_match_fields' => array(
				'tablenames' => 'pages',
			),
			'MM_insert_fields' => array(
				'tablenames' => 'pages',
			),
			'size' => 6,
			'autoSizeMax' => 30,
			'minitems' => 0,
			'maxitems' => 9999,
			'selectedListStyle' => 'width:400px;',
			'wizards' => array(
				'_PADDING' => 0,
				'_VERTICAL' => 0,
				'suggest' => array(
					'type' => 'suggest',
					'pages' => array(
						'searchWholePhrase' => 1,
					),
				),
			),
		),
	),
	'tx_bgmhreflang_2' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:bgm_hreflang/Resources/Private/Language/Backend.xlf:pages.tx_bgmhreflang_2',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'pages',
			'foreign_table' => 'pages',
			'MM' => 'tx_bgmhreflang_page_page_mm',
			'MM_match_fields' => array(
				'tablenames' => 'pages',
			),
			'MM_insert_fields' => array(
				'tablenames' => 'pages',
			),
			'MM_opposite_field' => 'tx_bgmhreflang_1',
			'size' => 6,
			'autoSizeMax' => 30,
			'minitems' => 0,
			'maxitems' => 9999,
			'selectedListStyle' => 'width:400px;',
			'disable_controls' => 'browser',
		),
	),
	'tx_bgmhreflang_list' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:bgm_hreflang/Resources/Private/Language/Backend.xlf:pages.tx_bgmhreflang_list',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'BGM\BgmHreflang\Utility\HreflangTags->renderBackendList',
		),
	),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_bgmhreflang', 'tx_bgmhreflang_1,--linebreak--,tx_bgmhreflang_2,--linebreak--,tx_bgmhreflang_list');
$GLOBALS['TCA']['pages']['palettes']['tx_bgmhreflang']['canNotCollapse'] = 1;
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', '--palette--;LLL:EXT:bgm_hreflang/Resources/Private/Language/Backend.xlf:pages.palette.tx_bgmhreflang;tx_bgmhreflang;;', '', 'after:lastUpdated');

$GLOBALS['TCA']['pages']['ctrl']['setToDefaultOnCopy'] = ($GLOBALS['TCA']['pages']['ctrl']['setToDefaultOnCopy'] ? $GLOBALS['TCA']['pages']['ctrl']['setToDefaultOnCopy'] . ',' : '') . 'tx_bgmhreflang_1,tx_bgmhreflang_2';
?>