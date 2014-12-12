<?php
namespace BGM\BgmHreflang\Utility;

class HreflangTags {

	/**
	 * t3lib_page object for finding rootline on the fly
	 *
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $sysPage;

	/**
	 * Render the related pages and the shortest path to them
	 *
	 * @param $content
	 * @param $parentObject
	 */
	public function renderBackendList($conf, $formEngineObject){
		$renderedList = '';
		if(intval($conf['row']['uid']) > 0) {
			$relations = $this->getCachedRelations($conf['row']['uid']);

			foreach($relations as $relatedPage => $info){
				$renderedListItem = '<li>' . \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordPath($relatedPage, '', 1000) . ' [' . $relatedPage . ']';
				$hreflangAttributes = array();
				foreach ($info['hreflangAttributes'] as $sysLanguageUid => $hreflangAttribute) {
					$hreflangAttributes[] = '<li>' . $hreflangAttribute . '</li>';
				}
				if (count($hreflangAttributes) > 0) {
					$renderedListItem .= '<ul style="list-style:disc inside; margin-left: 20px;">' . implode($hreflangAttributes) . '</ul>';
				}
				$renderedListItem .= '</li>';
				$renderedListItems[] = $renderedListItem;
			}
			sort($renderedListItems);
			$renderedList = '<ul>' . implode($renderedListItems) . '</ul>';
		}

		return $renderedList;
	}

	/**
	 * Renders the hreflang-tags
	 *
	 * @param string $content
	 * @param array $conf
	 * @return string
	 */
	public function renderFrontendList($content, $conf){
		$renderedList = array();
		if (intval($GLOBALS['TSFE']->id) > 0) {
			$currentGetParameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();
			if (isset($currentGetParameters['ZargesProducts'])) {
				$currentZargesProducts = $this->getCurrentZargesProducts($currentGetParameters['ZargesProducts']);
			}

			$relations = $this->getCachedRelations($GLOBALS['TSFE']->id);
			foreach ($relations as $relatedPage => $info) {
				foreach ($info['hreflangAttributes'] as $sysLanguageUid => $hreflangAttribute) {
					$getParameters = array_merge($currentGetParameters, array('L' => $sysLanguageUid));
					if(isset($currentGetParameters['ZargesProducts'])){
						$getParameters['ZargesProducts'] = array_merge($getParameters['ZargesProducts'], $this->getZargesProducts($currentZargesProducts, $relatedPage, $sysLanguageUid));
					}
					$renderedList[] = '<link rel="alternate" hreflang="' . $hreflangAttribute . '" href="' . \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($GLOBALS['TSFE']->cObj->currentPageUrl($getParameters, $relatedPage)) . '" />';
				}
			}
		}

		return $content . "\n" . implode($renderedList, "\n") . "\n";
	}

	/**
	 * Get hreflang relations from cache or generate the list and cache them
	 *
	 * @param integer $pageId
	 * @return array $relations
	 */
	protected function getCachedRelations($pageId){
		/** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheInstance */
		$cacheInstance = $GLOBALS['typo3CacheManager']->getCache('tx_bgmhreflang_cache');
		// If $relations is empty array, it hasn't been cached. Calculate the value and store it in the cache:
		$relationsFromCache = $cacheInstance->getByTag('pageId_' . $pageId);
		if(count($relationsFromCache)>0){
			$relations = $relationsFromCache[0];
		} else {
			$relations = array();
			$this->getRelations($pageId, $relations);
			//prepend each related page (= array_keys($relations)) with "pageId_" so this cache is cleared, when the
			//corresponding page cache is cleared (@see EXT:core/Classes/DataHandling/DataHandler.php::clear_cache())
			$tags = array_map(function ($value) {
				return 'pageId_' . $value;
			}, array_keys($relations));
			$cacheInstance->set($pageId, $relations, $tags, 84000);
		}

		return $relations;
	}

	/**
	 * Get hreflang relations recursivly
	 *
	 * @param integer $pageId
	 * @param array $relations
	 */
	protected function getRelations($pageId, &$relations) {
		$relations[$pageId]['hreflangAttributes'] = $this->getHreflangAttributes($pageId);

		$directRelations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_bgmhreflang_page_page_mm', 'uid_local=' . intval($pageId) . '');
		for ($i = 0; $i < count($directRelations); $i++) {
			if (!isset($relations[$directRelations[$i]['uid_foreign']])) {
				$this->getRelations($directRelations[$i]['uid_foreign'], $relations);
			}
		}

		$indirectRelations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_bgmhreflang_page_page_mm', 'uid_foreign=' . intval($pageId) . '');
		for ($i = 0; $i < count($indirectRelations); $i++) {
			if(!isset($relations[$indirectRelations[$i]['uid_local']])){
				$this->getRelations($indirectRelations[$i]['uid_local'], $relations);
			}
		}
	}

	/**
	 * Get the hreflangattributes for the default language and all translations of $pageId
	 *
	 * ATTENTION: Dirty hack to get configuration from RealUrl and to use International branch (6771) as x-default!
	 *
	 * @TODO: Check if $rootPageId is correct in FE
	 * @param integer $pageId
	 * @return array $hreflangAttributes
	 */
	protected function getHreflangAttributes($pageId) {
		$hreflangAttributes = array();

		$rootPageId = $this->getRootPageId($pageId);

		$realURLConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['www.zarges.com']['preVars'][0]['countryMapping'][intval($rootPageId)];

		$hreflangAttributes[] = ($rootPageId == 6771 ? 'x-default' : $realURLConf['languageMapping'][0] . '-' . $realURLConf['countryCode']);

		$translations = array_keys($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('sys_language_uid', 'pages_language_overlay', 'pid=' . intval($pageId) . ' AND deleted+hidden=0 ', '', '', '', 'sys_language_uid'));
		foreach ($translations as $translation) {
			$hreflangAttributes[$translation] = $realURLConf['languageMapping'][$translation] . ($rootPageId == 6771 ? '' : '-' . $realURLConf['countryCode']);
		}
		return $hreflangAttributes;
	}

	/**
	 * @param integer $pageId
	 * @return integer mixed
	 */
	protected function getRootPageId($pageId){
		if (TYPO3_MODE == 'BE') {
			$rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($pageId);
		} else {
			$this->createSysPageIfNecessary();
			$rootline = $this->sysPage->getRootLine($pageId);
		}
		foreach ($rootline as $rootlinePage) {
			if (intval($rootlinePage['is_siteroot']) == 1) {
				$rootPageId = $rootlinePage['uid'];
				break;
			}
		}

		return $rootPageId;
	}

	/**
	 * Creates $this->sysPage if it does not exist yet.
	 *
	 * @return void
	 */
	protected function createSysPageIfNecessary() {
		if (!is_object($this->sysPage)) {
			$this->sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			$this->sysPage->init($GLOBALS['TSFE']->showHiddenPage || $GLOBALS['TSFE']->beUserLogin);
		}
	}

	/**
	 * Gets the full records for the current TreeGroup, Product and ProductVariant
	 *
	 * @param array $zargesProduct
	 * @return array $currentZargesProducts
	 */
	protected function getCurrentZargesProducts($zargesProduct){
		$currentZargesProducts = array();
		if (intval($zargesProduct['treeGroup']) > 0) {
			$currentZargesProducts['treeGroup'] = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_zargesproducts_domain_model_treegroup', 'uid=' . intval($zargesProduct['treeGroup']));
		}
		if (intval($zargesProduct['product']) > 0) {
			$currentZargesProducts['product'] = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_zargesproducts_domain_model_product', 'uid=' . intval($zargesProduct['product']));
		}
		if (intval($zargesProduct['productVariant']) > 0) {
			$currentZargesProducts['productVariant'] = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_zargesproducts_domain_model_productvariant', 'uid=' . intval($zargesProduct['productVariant']));
		}

		return $currentZargesProducts;
	}

	/**
	 * Get the TreeGroup, Product and ProductVariant in the related page for the current TreeGroup, Product
	 * and ProductVariant
	 *
	 * @param array $currentZargesProducts
	 * @param integer $relatedPage
	 * @param integer $sysLanguageUid
	 * @return array $zargesProducts
	 */
	protected function getZargesProducts($currentZargesProducts, $relatedPage, $sysLanguageUid){
		$zargesProducts = array();
		$rootPageId = $this->getRootPageId($relatedPage);
		$storagePageId = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['www.zarges.com']['preVars'][0]['countryMapping'][intval($rootPageId)]['productStorage'];
		if(isset($currentZargesProducts['treeGroup'])) {
			$treeGroup = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid', 'tx_zargesproducts_domain_model_treegroup', 'mediando_refcode LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($currentZargesProducts['treeGroup']['mediando_refcode'] , 'tx_zargesproducts_domain_model_treegroup') . ' AND pid=' . intval($storagePageId) . ' AND sys_language_uid=' . intval($sysLanguageUid) . ' ' . $GLOBALS['TSFE']->sys_page->enableFields('tx_zargesproducts_domain_model_treegroup'));

			$zargesProducts['treeGroup'] = intval($treeGroup['uid']);
		}
		if(isset($currentZargesProducts['product'])) {
			$product = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid', 'tx_zargesproducts_domain_model_product', 'mediando_refcode LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($currentZargesProducts['product']['mediando_refcode'] , 'tx_zargesproducts_domain_model_product') . ' AND pid=' . intval($storagePageId) . ' AND sys_language_uid=' . intval($sysLanguageUid) . ' ' . $GLOBALS['TSFE']->sys_page->enableFields('tx_zargesproducts_domain_model_product'));

			$zargesProducts['product'] = intval($product['uid']);
		}
		if(isset($currentZargesProducts['productVariant'])) {
			$productVariant = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid', 'tx_zargesproducts_domain_model_productvariant', 'mediando_refcode LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($currentZargesProducts['productVariant']['mediando_refcode'] , 'tx_zargesproducts_domain_model_productvariant') . ' AND pid=' . intval($storagePageId) . ' AND sys_language_uid=' . intval($sysLanguageUid) . ' ' . $GLOBALS['TSFE']->sys_page->enableFields('tx_zargesproducts_domain_model_productvariant'));

			$zargesProducts['productVariant'] = intval($productVariant['uid']);
		}

		return $zargesProducts;
	}
}

?>