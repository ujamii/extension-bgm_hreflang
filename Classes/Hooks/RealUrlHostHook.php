<?php
namespace BGM\BgmHreflang\Hooks;

class RealUrlHostHook {

	public function getHost($params, $pObj){
	    $newHost = $params['host'];
	    if(isset($GLOBALS['TSFE']->register['buildHreflangLink']) && strlen($GLOBALS['TSFE']->register['buildHreflangLink']) > 0) {
            $newHost = $GLOBALS['TSFE']->register['buildHreflangLink'];
        }
	    return $newHost;
    }
}

?>
