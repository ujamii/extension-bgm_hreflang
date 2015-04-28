===============================================================
<link rel="alternate" hreflang="" href="" /> tags for TYPO3 CMS
===============================================================

I call them hreflang tags! :-)

More information about hreflang tags at Google: https://support.google.com/webmasters/answer/189077

.. figure:: Images/Editors.png
	:alt: Editors view
	:align: center

Why?
====

If you use the sys_languages for countries or if you have just one country with different languages, then there is no
need for this extension. Then you can build the hreflang tags with simple TypoScript.

But if you have multiple countries with multiple languages, then you will need this extension. Because then you will
have one page tree per country ("country branch") and TYPO3 CMS can not connect this country branches automatically.
The editors have to say, which pages from the different country branches belong together.

Prerequisites
=============

* One page tree per country ("country branch")
* Root of each country branch has the option "Use as Root Page" set

Configuration
=============

The configuration is done in the AdditionalConfiguration.php or your Theme-Extension's ext_localconf.php:

.. code:: php

	$languageMapping = array(

		//"sys_language_uid" and "isolanguagecode" have to be unique in the array $languageMapping!
		sys_language_uid => isolanguagecode,

		//Exaxample
		1 => 'de', //german
		11 => 'en', //english
		21 => 'it', //italian
		31 => 'fr', //french
	);

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'] = array(

		//"pageid" is the rootpage of a country branch. It has to be unique in the array $countryMapping!
		pageid => array(

			//"isocountrycode" has to be unique in the array $countryMapping!
			'countryCode' => isocountrycode,

			//'$languageMapping + array(0 => isolanguagecode)' can be assigned more than once with the same "isolanguagecode" in the array countryMapping.
			'languageMapping' => $languageMapping + array(0 => isolanguagecode),

			//This is optional. You need this, if you want the Germany country branch to be used as Austrian country branch, too.
			'additionalCountries' => array(isocountrycode, isocountrycode, ...),
		),

		//Example
		61 => array( //International
			'countryCode' => 'en',
			'languageMapping' => $languageMapping + array(0 => 'en'),
		),
		111 => array( //Germany and Austria
			'countryCode' => 'de',
			'languageMapping' => $languageMapping + array(0 => 'de'),
			'additionalCountries' => array('at'),
		),
		161 => array( //Switzerland
			'countryCode' => 'ch',
			'languageMapping' => $languageMapping + array(0 => 'de'),
		),
		211 => array( //Italy
			'countryCode' => 'it',
			'languageMapping' => $languageMapping + array(0 => 'it'),
		),
	);

	//If $_GET['L']==0, pages in this tree are rendered with hreflang="x-default", else only the isolanguagecode is used (without the isocountrycode)
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'] = 61;

And you need some TypoScript::

    page.headerData.30 = USER
    page.headerData.30 {
        userFunc = BGM\BgmHreflang\Utility\HreflangTags->renderFrontendList
    }

Usage
=====

Editors can connect the pages from the different country branches in the page properties.

Example
-------

Pagetree
````````

.. sidebar:: Pagetree

	.. figure:: Images/Pagetree.png
		:alt: Example pagetree
		:align: center

- 61 International
- - 71 Page A
- - - sys_language_uid 0 => english
- - 81 Page B
- - - sys_language_uid 0 => english
- - 91 Page C
- - - sys_language_uid 0 => english
- - 101 Page D
- - - sys_language_uid 0 => english
- - - sys_language_uid 1 => german
- 111 Deutschland
- - 121 Seite A
- - - sys_language_uid 0 => german
- - 131 Seite B
- - - sys_language_uid 0 => german
- - 141 Seite C
- - - sys_language_uid 0 => german
- - - sys_language_uid 11 => english
- - 151 Seite D
- - - sys_language_uid 0 => german
- 161 Schweiz
- - 201 Seite A
- - - sys_language_uid 0 => german
- - - sys_language_uid 21 => italian
- - - sys_language_uid 31 => french
- - 191 Seite B
- - - sys_language_uid 0 => german
- - - sys_language_uid 21 => italian
- - 181 Seite C
- - - sys_language_uid 0 => german
- - - sys_language_uid 31 => french
- - 171 Seite D
- - - sys_language_uid 0 => german
- 211 Italia
- - 251 Pagina A
- - - sys_language_uid 0 => italian
- - 241 Pagina B
- - - sys_language_uid 0 => italian
- - 231 Pagina C
- - - sys_language_uid 0 => italian
- - 221 Pagina D
- - - sys_language_uid 0 => italian
- - - sys_language_uid 1 => german

Configuration
`````````````

.. code:: php

	$languageMapping = array(
		1 => 'de', //german
		11 => 'en', //english
		21 => 'it', //italian
		31 => 'fr', //french
	);
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['countryMapping'] = array(
		//Example
		61 => array( //International
			'countryCode' => 'en',
			'languageMapping' => $languageMapping + array(0 => 'en'),
		),
		111 => array( //Germany and Austria
			'countryCode' => 'de',
			'languageMapping' => $languageMapping + array(0 => 'de'),
			'additionalCountries' => array('at'),
		),
		161 => array( //Switzerland
			'countryCode' => 'ch',
			'languageMapping' => $languageMapping + array(0 => 'de'),
		),
		211 => array( //Italy
			'countryCode' => 'it',
			'languageMapping' => $languageMapping + array(0 => 'it'),
		),
	);
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bgm_hreflang']['defaultCountryId'] = 61;

Output
``````

**1) The editor connected all A pages.**
So we have these hreflang tags on the A pages:

.. code:: html

	<link rel="alternate" hreflang="x-default" href="http://development.bgm.projects.localhost/index.php?id=71" />
	<link rel="alternate" hreflang="de-de" href="http://development.bgm.projects.localhost/index.php?id=121" />
	<link rel="alternate" hreflang="de-at" href="http://development.bgm.projects.localhost/index.php?id=121" />
	<link rel="alternate" hreflang="de-ch" href="http://development.bgm.projects.localhost/index.php?id=201" />
	<link rel="alternate" hreflang="it-ch" href="http://development.bgm.projects.localhost/index.php?id=201&L=21" />
	<link rel="alternate" hreflang="fr-ch" href="http://development.bgm.projects.localhost/index.php?id=201&L=31" />
	<link rel="alternate" hreflang="it-it" href="http://development.bgm.projects.localhost/index.php?id=251" />

**2) The editor connected the B pages 81, 131 and 241 (he has forgotten to connect the swiss B page 191 ;-)).**
So we have these hreflang tags on the B pages 81, 131 and 241:

.. code:: html

	<link rel="alternate" hreflang="x-default" href="http://development.bgm.projects.localhost/index.php?id=81" />
	<link rel="alternate" hreflang="de-de" href="http://development.bgm.projects.localhost/index.php?id=131" />
	<link rel="alternate" hreflang="de-at" href="http://development.bgm.projects.localhost/index.php?id=131" />
	<link rel="alternate" hreflang="it-it" href="http://development.bgm.projects.localhost/index.php?id=241" />

And we have these tags on the swiss B page 191:

.. code:: html

	<link rel="alternate" hreflang="de-ch" href="http://development.bgm.projects.localhost/index.php?id=191" />
	<link rel="alternate" hreflang="it-ch" href="http://development.bgm.projects.localhost/index.php?id=191&L=21" />

**3) The international C page 91 is connected to the german C page 141. And the german C page 141 is connected to the
italian C page 231.**

.. code:: html

	<link rel="alternate" hreflang="de-de" href="http://development.bgm.projects.localhost/index.php?id=141" />
	<link rel="alternate" hreflang="de-at" href="http://development.bgm.projects.localhost/index.php?id=141" />
	<link rel="alternate" hreflang="it-it" href="http://development.bgm.projects.localhost/index.php?id=231" />
	<link rel="alternate" hreflang="x-default" href="http://development.bgm.projects.localhost/index.php?id=91" />

**4) The swiss C page 181 is not connected to any other page and has a translation.**

.. code:: html

	<link rel="alternate" hreflang="de-ch" href="http://development.bgm.projects.localhost/index.php?id=181" />
	<link rel="alternate" hreflang="fr-ch" href="http://development.bgm.projects.localhost/index.php?id=181&L=31" />

**5) The international D page 101 is not connected to another page and has a translation.**

.. code:: html

	<link rel="alternate" hreflang="x-default" href="http://development.bgm.projects.localhost/index.php?id=101" />
	<link rel="alternate" hreflang="de" href="http://development.bgm.projects.localhost/index.php?id=101&L=1" />

**6) The german D page 151 is not connected to any other page and has no translation, but should be used for Austria, too.**

.. code:: html

	<link rel="alternate" hreflang="de-de" href="http://development.bgm.projects.localhost/index.php?id=151" />
	<link rel="alternate" hreflang="de-at" href="http://development.bgm.projects.localhost/index.php?id=151" />

Developers
==========

There are a lot of signals at different places in the code. Feel free to use them :-)

Example
-------

If you have product records in each country branch, but the EAN is the same, you could connect the products detail
view automatically depending on the EAN:

.. code:: php

	//include this in your AdditionalConfiguration.php or your Theme-Extension's ext_localconf.php
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')
		->connect(
			'BGM\\BgmHreflang\\Utility\\HreflangTags',
			'frontend_beforeRenderSingleTag',
			'BGM\\BgmTheme\\SignalSlot\\HreflangTags',
			'getGetParametersForProducts'
		);

See the implementation in :download:`the example script (EXT:bgm_hreflang/Documentation/Example/Products.php) <Example/Products.php>`.
Don't forget to connect the detail view pages in the backend! This class just adds the necessary GET parameters.