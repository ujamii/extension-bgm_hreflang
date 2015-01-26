===========================
hreflang tags for TYPO3 CMS
===========================

Redaktionell bearbeitbare hreflang-Tags.

Infos zu hreflang-Tags bei Google: https://support.google.com/webmasters/answer/189077

Configuration
=============

Die Konfiguration erfolgt in der AdditionalConfiguration.php oder der ext_localconf.php der Theme-Extension.

Example configuration::

	//"sys_language_uid" and "isolanguagecode" have to be unique in the array $languageMapping!
	$languageMapping = array(
		//sys_language_uid => isolanguagecode,
		1 => 'de', //Deutsch
		2 => 'en', //Englisch
		3 => 'fr', //Französisch
	);
	//"pageid" is the rootpage of a country tree. It has to be unique in the array $countryMapping!
	//"isocountrycode" has to be unique in the array $countryMapping!
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'] = array(
		pageid => array(
			'countryCode' => isocountrycode,
			//"$languageMapping + array(0 => isolanguagecode)" can be assigned more than once with the same isolanguagecode as languageMapping in the array countryMapping.
			'languageMapping' => $languageMapping + array(0 => isolanguagecode),
			//"additionalCountries" is optional
			'additionalCountries' => array(isocountrycode2, isocountrycode3),
		),

		12 => array( //International
			'countryCode' => 'en',
			'languageMapping' => $languageMapping + array(0 => 'en'),
		),
		34 => array( //Deutschland
			'countryCode' => 'de',
			'languageMapping' => $languageMapping + array(0 => 'de'),
			'additionalCountries' => array('at', 'ch'),
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
	//If $_GET['L']==0, pages in this tree are rendered with 'x-default', else only the isolanguagecode is used (without the isocountrycode)
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'] = 12;

Außerdem braucht man noch ein bisschen TypoScript::

    page.headerData.30 = USER
    page.headerData.30 {
        userFunc = \BGM\BgmHreflang\Utility\HreflangTags->renderFrontendList
    }

Usage
=====

Redakteure können in den Seiteneigenschaften Seiten aus verschiedenen Länderästen miteinander verknüpfen

Example
-------

Seitenbaum::

	- 1 root
	-- 2 Deutschland (soll auch für Österreich gelten)
	--- 3 Seite XYZ (Sprachen: 0 deutsch)
	--- 10 Seite ABC (Sprachen: 0 deutsch)
	-- 4 Frankreich
	--- 5 Seite XYZ (Sprachen: 0 französisch)
	--- 11 Seite ABC (Sprachen: 0 französisch)
	--- 14 Seite DEF (Sprachen: 0 französisch)
	-- 6 Schweiz
	--- 7 Seite XYZ (Sprachen: 0 deutsch, 2 französisch, 3 italienisch)
	--- 12 Seite ABC (Sprachen: 0 deutsch, 3 italienisch)
	--- 15 Seite WER (Sprachen: 0 deutsch, 3 italienisch)
	-- 8 International
	--- 9 Seite XYZ (Sprachen: 0 englisch, 1 deutsch)
	--- 13 Seite ABC (Sprachen: 0 englisch, 1 deutsch)

Konfiguration::

	$languageMapping = array(
		1 => 'de', //deutsch
		2 => 'fr', //französisch
		3 => 'it', //italienisch
	);
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'] = array(
		2 => array(
			'countryCode' => 'de',
			'languageMapping' => $languageMapping + array(0 => 'de'),
			'additionalCountries' => array('at'),
		),
		4 => array(
			'countryCode' => 'fr',
			'languageMapping' => $languageMapping + array(0 => 'fr'),
		),
		6 => array(
			'countryCode' => 'ch',
			'languageMapping' => $languageMapping + array(0 => 'de'),
		),
		8 => array(
			'countryCode' => 'en',
			'languageMapping' => $languageMapping + array(0 => 'en'),
		),
	);
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'] = 8;

Der Redakteur hat alle XYZ-Seiten miteinander verknüpft. Daraus ergeben sich diese Tags auf den XYZ-Seiten::

	<link rel="alternate" hreflang="de-de" href="http://domain.tld/index.php?id=3 />
	<link rel="alternate" hreflang="de-at" href="http://domain.tld/index.php?id=3 />
	<link rel="alternate" hreflang="fr-fr" href="http://domain.tld/index.php?id=5 />
	<link rel="alternate" hreflang="de-ch" href="http://domain.tld/index.php?id=7 />
	<link rel="alternate" hreflang="fr-ch" href="http://domain.tld/index.php?id=7&L=2 />
	<link rel="alternate" hreflang="it-ch" href="http://domain.tld/index.php?id=7&L=3 />
	<link rel="alternate" hreflang="x-default" href="http://domain.tld/index.php?id=9" />
	<link rel="alternate" hreflang="de" href="http://domain.tld/index.php?id=9&L=1" />

Der Redakteur hat die ABC-Seiten 10, 11 und 12 miteinander verknüpft (13 hat er vergessen ;-)). Daraus ergeben sich
diese Tags auf den ABC-Seiten 10, 11 und 12::

	<link rel="alternate" hreflang="de-de" href="http://domain.tld/index.php?id=10 />
	<link rel="alternate" hreflang="de-at" href="http://domain.tld/index.php?id=10 />
	<link rel="alternate" hreflang="fr-fr" href="http://domain.tld/index.php?id=11 />
	<link rel="alternate" hreflang="de-ch" href="http://domain.tld/index.php?id=12 />
	<link rel="alternate" hreflang="it-ch" href="http://domain.tld/index.php?id=12&L=3 />

Und auf der Seite 13 werden nur diese Tags ausgegeben::

	<link rel="alternate" hreflang="x-default" href="http://domain.tld/index.php?id=13" />
	<link rel="alternate" hreflang="de" href="http://domain.tld/index.php?id=13&L=1" />

Auf der Seite DEF (14) wird nur dieser Tag ausgegeben (ein Land, eine Sprache, nicht verknüpft)::

	<link rel="alternate" hreflang="fr-fr" href="http://domain.tld/index.php?id=14 />

Auf der Seite WER (15) werden diese Tags ausgegeben (ein Land, zwei Sprache, nicht verknüpft)::

	<link rel="alternate" hreflang="de-ch" href="http://domain.tld/index.php?id=15 />
	<link rel="alternate" hreflang="it-ch" href="http://domain.tld/index.php?id=15&L=3 />

Developers
==========

Es gibt mehrere Signals an diversen Stellen in der Extension. Diese können genutzt werden, um die hreflang-Tags zu
beeinflussen.

Bei ZARGES ist zum Beispiel ein automatisches Mapping der Produkte zwischen den Länderästen möglich. Dadurch können
die Tags auf den Produktdetailseiten automatisch erzeugt werden.
https://gitlab.bgm-gmbh.de/zarges/internet/blob/master/typo3conf/ext/bgm_theme_zarges/Classes/SignalSlot/HreflangTags.php