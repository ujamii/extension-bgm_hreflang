<?php
namespace BGM\BgmHreflang\Hooks;

class ClearCacheHook {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	protected $cacheInstance;

	public function __construct() {
		$this->initializeCache();
	}

	/**
	 * @param \array $_params
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
	 */
	public function clear($_params, $dataHandler) {
		if ($_params['cacheCmd'] == 'pages') {
			$this->cacheInstance->flush();
		} else if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($_params['cacheCmd'])) {
			$this->cacheInstance->flushByTag('pageId_' . $_params['cacheCmd']);
		}
	}

	/**
	 * Initialize cache instance to be ready to use
	 *
	 * @return void
	 */
	protected function initializeCache() {
		\TYPO3\CMS\Core\Cache\Cache::initializeCachingFramework();
		try {
			$this->cacheInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('tx_bgmhreflang_cache');
		} catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
			$this->cacheInstance = $GLOBALS['typo3CacheFactory']->create(
				'tx_bgmhreflang_cache',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache']['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache']['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_bgmhreflang_cache']['options']
			);
		}
	}
}

?>