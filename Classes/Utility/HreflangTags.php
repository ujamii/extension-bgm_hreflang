<?php
namespace BGM\BgmHreflang\Utility;

class HreflangTags {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	protected $cacheInstance;

	/**
	 * t3lib_page object for finding rootline on the fly
	 *
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $sysPage;

	/**
	 * current $_GET parameters
	 *
	 * @var array
	 * @see renderBackendList(), renderFrontendList()
	 */
	protected $getParameters;

	/**
	 * current related page
	 *
	 * @var integer
	 * @see renderBackendList(), renderFrontendList()
	 */
	protected $relatedPage;

	/**
	 * current hreflang attribute for the related page
	 *
	 * @var string
	 * @see renderBackendList(), renderFrontendList()
	 */
	protected $hreflangAttribute;

	/**
	 * curent hreflang attributes for the related page
	 *
	 * @var array
	 * @see renderBackendList(), renderFrontendList()
	 */
	protected $hreflangAttributes;

	/**
	 * additional parameters for the current hreflang attribute $hreflangAttribute.
	 * contains the keys sysLanguageUid and mountPoint
	 *
	 * @var array
	 * @see renderBackendList(), renderFrontendList()
	 */
	protected $additionalParameters;

	/**
	 * rendered item
	 *
	 * @var string
	 * @see renderBackendList(), renderFrontendList()
	 */
	protected $renderedListItem;

	public function __construct(){
		$this->initializeCache();
	}

	/**
	 * Render the related pages and the shortest path to them
	 *
	 * @param $content
	 * @param $parentObject
	 */
	public function renderBackendList($conf, $formEngineObject){
		$renderedList = '';
		if(intval($conf['row']['uid']) > 0) {
			/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
			$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

			$relations = $this->getCachedRelations($conf['row']['uid']);

			foreach($relations as $this->relatedPage => $info){
				$signalSlotDispatcher->dispatch(__CLASS__, 'backend_beforeRenderSinglePage', array($this));
				$this->renderedListItem = '<li>' . \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordPath($this->relatedPage, '', 1000) . ' [' . $this->relatedPage . ']';
				$this->hreflangAttributes = array();
				foreach ($info as $this->hreflangAttribute => $this->additionalParameters) {
					$signalSlotDispatcher->dispatch(__CLASS__, 'backend_beforeRenderSingleHreflangAttribute', array($this));
					$this->hreflangAttributes[] = '<li>' . $this->hreflangAttribute . (strlen($this->additionalParameters['mountPoint']) > 0 ? ' (MountPoint ' . $this->additionalParameters['mountPoint'] . ')' : '') . (intval($this->additionalParameters['sysLanguageUid']) > 0 ? ' (SysLanguageUid ' . $this->additionalParameters['sysLanguageUid'] . ')': '') .'</li>';
					$signalSlotDispatcher->dispatch(__CLASS__, 'backend_afterRenderSingleHreflangAttribute', array($this));
				}
				if (count($this->hreflangAttributes) > 0) {
					$this->renderedListItem .= '<ul style="list-style:disc inside; margin-left: 20px;">' . implode($this->hreflangAttributes) . '</ul>';
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
		$renderedListItems = array();
		if (intval($GLOBALS['TSFE']->id) > 0) {
			/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
			$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

			$this->getParameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();

			$relations = $this->getCachedRelations($GLOBALS['TSFE']->id);

			$mpdisable = $GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'];
			$GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'] = 1;
			foreach ($relations as $this->relatedPage => $info) {
				foreach ($info as $this->hreflangAttribute => $this->additionalParameters) {
					unset($this->getParameters['id']);
					unset($this->getParameters['L']);
					if(intval($this->additionalParameters['sysLanguageUid']) > 0){
						$this->getParameters['L'] = $this->additionalParameters['sysLanguageUid'];
					}
					unset($this->getParameters['MP']);
					if(strlen($this->additionalParameters['mountPoint']) > 0){
						$this->getParameters['MP'] = $this->additionalParameters['mountPoint'];
					}

					$signalSlotDispatcher->dispatch(__CLASS__, 'frontend_beforeRenderSingleTag', array($this));
					$this->renderedListItem = '<link rel="alternate" hreflang="' . $this->hreflangAttribute . '" href="' . \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($GLOBALS['TSFE']->cObj->currentPageUrl($this->getParameters, $this->relatedPage)) . '" />';
					$signalSlotDispatcher->dispatch(__CLASS__, 'frontend_afterRenderSingleTag', array($this));
					$renderedListItems[] = $this->renderedListItem;
				}
			}
			sort($renderedListItems);
			$GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'] = $mpdisable;
		}

		return $content . "\n" . implode($renderedListItems, "\n") . "\n";
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
	 * @param string $additionalParameters
	 */
	public function setAdditionalParameters($additionalParameters){
		$this->additionalParameters = $additionalParameters;
	}

	/**
	 * @return string
	 */
	public function getAdditionalParameters() {
		return $this->additionalParameters;
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
		// get relations from cache
		$cacheIdentifier = $pageId;
		$cacheTag = 'pageId_' . $pageId;
		$relationsFromCache = $this->cacheInstance->getByTag($cacheTag);
		//Check, if the current page is already cached
		if(count($relationsFromCache)>0 && is_array($relationsFromCache[0][$cacheIdentifier])){
			$relations = $relationsFromCache[0];
		} else {
		// If $relationsFromCache is empty array, it hasn't been cached. Calculate the value and store it in the cache:
			$relations = array();
			$this->buildRelations($pageId, $relations);
			// prepend each related page (= array_keys($relations)) with "pageId_" and use this as tag. So this cache is
			// cleared, when the corresponding page cache is cleared
			// @see EXT:core/Classes/DataHandling/DataHandler.php::clear_cache()
			$tags = array_map(function ($value) {
				return 'pageId_' . $value;
			}, array_keys($relations));
			foreach($tags as $tag){
				$this->cacheInstance->flushByTag($tag);
			}
			$this->cacheInstance->set($cacheIdentifier, $relations, $tags, 84000);
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
		$relations[$pageId] = $this->buildHreflangAttributes($pageId);

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
	 * @param integer $pageId
	 * @param string $mountPoint
	 * @return array $hreflangAttributes
	 */
	protected function buildHreflangAttributes($pageId, $mountPoint='') {
		$hreflangAttributes = array();

		$rootline = $this->getRootLine($pageId, $mountPoint);
		$rootPageId = $this->getRootPageId($rootline);

		$countryMapping = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'][intval($rootPageId)];
		$defaultCountryId = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'];

		$hreflangAttributes[($rootPageId == $defaultCountryId ? 'x-default' : $countryMapping['languageMapping'][0] . '-' . $countryMapping['countryCode'])] = array(
			'sysLanguageUid' => 0,
			'mountPoint' => $mountPoint,
		);

		$translations = array_keys($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('sys_language_uid', 'pages_language_overlay', 'pid=' . intval($pageId) . ' AND deleted+hidden=0 ', '', '', '', 'sys_language_uid'));
		foreach ($translations as $translation) {
			$hreflangAttributes[$countryMapping['languageMapping'][$translation] . ($rootPageId == $defaultCountryId ? '' : '-' . $countryMapping['countryCode'])] = array(
				'sysLanguageUid' => $translation,
				'mountPoint' => $mountPoint,
			);
		}

		if($countryMapping['additionalCountries']){
			foreach($countryMapping['additionalCountries'] as $additionalCountry){
				$hreflangAttributes[$countryMapping['languageMapping'][0] . '-' . $additionalCountry] = array(
					'sysLanguageUid' => 0,
					'mountPoint' => $mountPoint,
				);
				foreach ($translations as $translation) {
					$hreflangAttributes[$countryMapping['languageMapping'][$translation] . '-' . $additionalCountry] = array(
						'sysLanguageUid' => $translation,
						'mountPoint' => $mountPoint,
					);
				}
			}
		}

		if(strlen($mountPoint) == 0){ //@TODO nested mountpoints are to expensive
			//check, if the current page is mounted somewhere
			$mountPoints = $this->getMountpoints($rootline);
			if(count($mountPoints) > 0){
				foreach($mountPoints as $mountPoint){
					$hreflangAttributes = array_merge($hreflangAttributes, $this->buildHreflangAttributes($pageId, $mountPoint['mountPoint']));
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
	 * Search for pages in the $rootline, which are mounted somewhere and return an array with mpvars
	 *
	 * @param array $rootline
	 * @return mixed
	 */
	protected function getMountPoints($rootline){
		$rootlineIds = array();
		foreach($rootline as $page){
			$rootlineIds[] = $page['uid'];
		}
		$mountPoints = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('CONCAT(mount_pid, "-", uid) as mountPoint', 'pages', 'doktype = 7 AND mount_pid IN (' . implode(',', $rootlineIds) . ') AND deleted+hidden = 0');

		return $mountPoints;
	}

	/**
	 * Get the rootline for $pageId and $mountPoint (mpvar)
	 *
	 * @param integer $pageId
	 * @param string $mountPoint
	 * @return array
	 */
	protected function getRootLine($pageId, $mountPoint = ''){
		$this->createSysPageIfNecessary();
		$rootline = $this->sysPage->getRootLine($pageId, $mountPoint);

		return $rootline;
	}

	/**
	 * Search for the closest page with is_siteroot=1 in the rootline
	 *
	 * @param array $rootline
	 * @return int
	 */
	protected function getRootPageId($rootline){
		$rootPageId = 0;
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
	 * Initialize cache instance to be ready to use
	 *
	 * @return void
	 */
	protected function initializeCache() {
		$this->cacheInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('tx_bgmhreflang_cache');
	}
}

?>