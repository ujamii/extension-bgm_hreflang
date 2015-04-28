.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developers:

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