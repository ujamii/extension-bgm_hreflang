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
	 * @var array
	 */
	protected $getParameters;

	/**
	 * @var integer
	 */
	protected $relatedPage;

	/**
	 * @var string
	 */
	protected $hreflangAttribute;

	/**
	 * @var array
	 */
	protected $hreflangAttributes;

	/**
	 * @var string
	 */
	protected $hrefAttribute;

	/**
	 * @var string
	 */
	protected $renderedListItem;

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
			/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
			$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

			foreach($relations as $this->relatedPage => $info){
				$signalSlotDispatcher->dispatch(__CLASS__, 'backend_beforeRenderSinglePage', array($this));
				$this->renderedListItem = '<li>' . \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordPath($this->relatedPage, '', 1000) . ' [' . $this->relatedPage . ']';
				$hreflangAttributes = array();
				foreach ($info['hreflangAttributes'] as $sysLanguageUid => $this->hreflangAttribute) {
					$signalSlotDispatcher->dispatch(__CLASS__, 'backend_beforeRenderSingleHreflangAttribute', array($this));
					$hreflangAttributes[] = '<li>' . $this->hreflangAttribute . '</li>';
					$signalSlotDispatcher->dispatch(__CLASS__, 'backend_afterRenderSingleHreflangAttribute', array($this));
				}
				if (count($hreflangAttributes) > 0) {
					$this->renderedListItem .= '<ul style="list-style:disc inside; margin-left: 20px;">' . implode($hreflangAttributes) . '</ul>';
				}
				$this->renderedListItem .= '</li>';
				$signalSlotDispatcher->dispatch(__CLASS__, 'backend_afterRenderSinglePage', array($this));
				$renderedListItems[] = $this->renderedListItem;
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
			/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
			$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
			$this->getParameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();

			// disable the mountpoint rendering
			unset($this->getParameters['MP']);
			$mpdisable = $GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'];
			$GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'] = 1;

			$relations = $this->getCachedRelations($GLOBALS['TSFE']->id);
			foreach ($relations as $this->relatedPage => $info) {
				foreach ($info['hreflangAttributes'] as $sysLanguageUid => $this->hreflangAttribute) {
					$this->getParameters['L'] = $sysLanguageUid;
					$signalSlotDispatcher->dispatch(__CLASS__, 'frontend_beforeRenderSingleTag', array($this));
					$this->renderedListItem = '<link rel="alternate" hreflang="' . $this->hreflangAttribute . '" href="' . \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($GLOBALS['TSFE']->cObj->currentPageUrl($this->getParameters, $this->relatedPage)) . '" />';
					$signalSlotDispatcher->dispatch(__CLASS__, 'frontend_afterRenderSingleTag', array($this));
					$renderedList[] = $this->renderedListItem;
				}
			}

			// enable mountpoint rendering
			$GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'] = $mpdisable;
		}

		return $content . "\n" . implode($renderedList, "\n") . "\n";
	}

	/**
	 * @param array $getParameters
	 */
	public function setGetParameters($getParameters){
		$this->getParameters = $getParameters;
	}

	/**
	 * @return array
	 */
	public function getGetParameters() {
		return $this->getParameters;
	}

	/**
	 * @param integer $relatedPage
	 */
	public function setRelatedPage($relatedPage){
		$this->relatedPage = $relatedPage;
	}

	/**
	 * @return int
	 */
	public function getRelatedPage() {
		return $this->relatedPage;
	}

	/**
	 * @param string $hreflangAttribute
	 */
	public function setHreflangAttribute($hreflangAttribute){
		$this->hreflangAttribute = $hreflangAttribute;
	}

	/**
	 * @return string
	 */
	public function getHreflangAttribute() {
		return $this->hreflangAttribute;
	}

	/**
	 * @param array $hreflangAttributes
	 */
	public function setHreflangAttributes($hreflangAttributes){
		$this->hreflangAttributes = $hreflangAttributes;
	}

	/**
	 * @return array
	 */
	public function getHreflangAttributes() {
		return $this->hreflangAttributes;
	}

	/**
	 * @param string $hrefAttribute
	 */
	public function setHrefAttribute($hrefAttribute){
		$this->hrefAttribute = $hrefAttribute;
	}

	/**
	 * @return string
	 */
	public function getHrefAttribute() {
		return $this->hrefAttribute;
	}

	/**
	 * @param string $renderedListItem
	 */
	public function setRenderedListItem($renderedListItem){
		$this->renderedListItem = $renderedListItem;
	}

	/**
	 * @return string
	 */
	public function getRenderedListItem() {
		return $this->renderedListItem;
	}

	/**
	 * Get hreflang relations from cache or generate the list and cache them
	 *
	 * @param integer $pageId
	 * @return array $relations
	 */
	public function getCachedRelations($pageId){
		/** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheInstance */
		$cacheInstance = $GLOBALS['typo3CacheManager']->getCache('tx_bgmhreflang_cache');
		// If $relations is empty array, it hasn't been cached. Calculate the value and store it in the cache:
		$relationsFromCache = $cacheInstance->getByTag('pageId_' . $pageId);
		if(count($relationsFromCache)>0){
			$relations = $relationsFromCache[0];
		} else {
			$relations = array();
			$this->buildRelations($pageId, $relations);
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
	protected function buildRelations($pageId, &$relations) {
		$relations[$pageId]['hreflangAttributes'] = $this->buildHreflangAttributes($pageId);

		$directRelations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_bgmhreflang_page_page_mm', 'uid_local=' . intval($pageId) . '');
		for ($i = 0; $i < count($directRelations); $i++) {
			if (!isset($relations[$directRelations[$i]['uid_foreign']])) {
				$this->buildRelations($directRelations[$i]['uid_foreign'], $relations);
			}
		}

		$indirectRelations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_bgmhreflang_page_page_mm', 'uid_foreign=' . intval($pageId) . '');
		for ($i = 0; $i < count($indirectRelations); $i++) {
			if(!isset($relations[$indirectRelations[$i]['uid_local']])){
				$this->buildRelations($indirectRelations[$i]['uid_local'], $relations);
			}
		}
	}

	/**
	 * Get the hreflangattributes for the default language and all translations of $pageId
	 *
	 * @TODO: Check if $rootPageId is correct in FE
	 * @param integer $pageId
	 * @return array $hreflangAttributes
	 */
	protected function buildHreflangAttributes($pageId) {
		$hreflangAttributes = array();

		$rootPageId = $this->getRootPageId($pageId);

		$countryMapping = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'][intval($rootPageId)];
		$defaultCountryId = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'];

		$hreflangAttributes[] = ($rootPageId == $defaultCountryId ? 'x-default' : $countryMapping['languageMapping'][0] . '-' . $countryMapping['countryCode']);

		$translations = array_keys($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('sys_language_uid', 'pages_language_overlay', 'pid=' . intval($pageId) . ' AND deleted+hidden=0 ', '', '', '', 'sys_language_uid'));
		foreach ($translations as $translation) {
			$hreflangAttributes[] = $countryMapping['languageMapping'][$translation] . ($rootPageId == $defaultCountryId ? '' : '-' . $countryMapping['countryCode']);
		}

		if($countryMapping['additionalCountries']){
			foreach($countryMapping['additionalCountries'] as $additionalCountry){
				$hreflangAttributes[] = $countryMapping['languageMapping'][0] . '-' . $additionalCountry;
				foreach ($translations as $translation) {
					$hreflangAttributes[] = $countryMapping['languageMapping'][$translation] . '-' . $additionalCountry;
				}
			}
		}

		$this->hreflangAttributes = $hreflangAttributes;

		/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
		$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
		$signalSlotDispatcher->dispatch(__CLASS__, 'buildHreflangAttributes', array($this));

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
}

?>