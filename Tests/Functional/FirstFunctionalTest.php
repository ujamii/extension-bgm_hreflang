<?php
namespace BGM\BgmHreflang\Tests\Functional;

/**
 * Class FirstFunctionalTest
 * https://de.slideshare.net/cpsitgmbh/functional-tests-with-typo3
 * typo3DatabaseName="bgm_hreflang" typo3DatabaseUsername="project" typo3DatabasePassword="project" typo3DatabaseHost="127.0.0.1" typo3DatabasePort="3308" TYPO3_PATH_WEB="$PWD/.Build/Web" $PWD/.Build/bin/phpunit -c $PWD/.Build/vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml $PWD/.Build/Web/typo3conf/ext/bgm_hreflang/Tests/Functional
 *
 * @package BGM\BgmHreflang\Tests\Functional
 */
class FirstFunctionalTest extends \Nimut\TestingFramework\TestCase\FunctionalTestCase {

    /**
     * Ensure your extension is loaded
     *
     * @var array
     */
    protected $testExtensionsToLoad = array(
        'typo3conf/ext/bgm_hreflang',
        );

    protected $configurationToUseInTestInstance = array(
        'EXTCONF' => array(
            'bgm_hreflang' => array(
                'countryMapping' => array(
                    2 => array( //International
                        'countryCode' => 'en',
                        'languageMapping' => array(0 => 'en'),
                        'domainName' => 'https://www.my-domain.com',
                    ),
                    8 => array( //Germany and Austria
                        'countryCode' => 'de',
                        'languageMapping' => array(0 => 'de'),
                        'additionalCountries' => array('at'),
                    ),
                    14 => array( //Switzerland
                        'countryCode' => 'ch',
                        'languageMapping' => array(0 => 'de', 1 => 'it', 2 => 'fr',),
                        'additionalGetParameters' => array(
                            0 => '&foo=bar',
                            2 => '&foo=bar&john=doe',
                        ),
                    ),
                ),
                'defaultCountryId' => 2,
            ),
        ),
    );

    protected $fixturePath;

    protected function setUp()
    {
        parent::setUp();

        $this->fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/bgm_hreflang/Tests/Functional/Fixtures/';

        // Import own fixtures
        $this->importDataSet($this->fixturePath . 'Database/pages.xml');
        $this->importDataSet($this->fixturePath . 'Database/pages_language_overlay.xml');
        $this->importDataSet($this->fixturePath . 'Database/sys_language.xml');
        $this->importDataSet($this->fixturePath . 'Database/tx_bgmhreflang_page_page_mm.xml');
        $this->importDataSet($this->fixturePath . 'Database/be_users.xml');

        // Set up the frontend!
        $this->setUpFrontendRootPage(1, // page id
            array( // array of TypoScript files which should be included
                $this->fixturePath . 'Frontend/Page.ts'
            ));
    }

    /**
     * Page "International-1" is connected with "Deutschland-1" and "Schweiz-1"
     *
     * @test
     */
    public function international1PageOutput()
    {

        $response = $this->getFrontendResponse(3);
        $this->assertEquals(
            trim('
<link rel="alternate" hreflang="de-at" href="http://localhost/index.php?id=9" />
<link rel="alternate" hreflang="de-ch" href="http://localhost/index.php?id=15&foo=bar" />
<link rel="alternate" hreflang="de-de" href="http://localhost/index.php?id=9" />
<link rel="alternate" hreflang="fr-ch" href="http://localhost/index.php?id=15&L=2&foo=bar&john=doe" />
<link rel="alternate" hreflang="it-ch" href="http://localhost/index.php?id=15&L=1" />
<link rel="alternate" hreflang="x-default" href="https://www.my-domain.com/index.php?id=3" />
            '),
            trim($response->getContent())
        );
    }

    /**
     * Page "International-2" is connected with "Deutschland-2" and mounted to "Schweiz-2"
     *
     * @test
     */
    public function international2PageOutput()
    {

        $response = $this->getFrontendResponse(4);
        $this->assertEquals(
            trim('
<link rel="alternate" hreflang="de-at" href="http://localhost/index.php?id=10" />
<link rel="alternate" hreflang="de-ch" href="http://localhost/index.php?id=4&MP=4-16&foo=bar" />
<link rel="alternate" hreflang="de-de" href="http://localhost/index.php?id=10" />
<link rel="alternate" hreflang="x-default" href="https://www.my-domain.com/index.php?id=4" />
            '),
            trim($response->getContent())
        );
    }

    /**
     * Page "International-3" is connected with "Deutschland-3" and "Deutschland-3" is connected to "Schweiz-3"
     *
     * @test
     */
    public function international3PageOutput()
    {

        $response = $this->getFrontendResponse(6);
        $this->assertEquals(
            trim('
<link rel="alternate" hreflang="de-at" href="http://localhost/index.php?id=12" />
<link rel="alternate" hreflang="de-ch" href="http://localhost/index.php?id=17&foo=bar" />
<link rel="alternate" hreflang="de-de" href="http://localhost/index.php?id=12" />
<link rel="alternate" hreflang="it-ch" href="http://localhost/index.php?id=17&L=1" />
<link rel="alternate" hreflang="x-default" href="https://www.my-domain.com/index.php?id=6" />
            '),
            trim($response->getContent())
        );
    }

    /**
     * Page "International-4" is not connected
     *
     * @test
     */
    public function international4PageOutput()
    {

        $response = $this->getFrontendResponse(7);
        $this->assertEquals(
            trim('
<link rel="alternate" hreflang="x-default" href="https://www.my-domain.com/index.php?id=7" />
            '),
            trim($response->getContent())
        );
    }

    /**
     * Page "Deutschland-4" is not connected
     *
     * @test
     */
    public function deutschland4PageOutput()
    {

        $response = $this->getFrontendResponse(13);
        $this->assertEquals(
            trim('
<link rel="alternate" hreflang="de-at" href="http://localhost/index.php?id=13" />
<link rel="alternate" hreflang="de-de" href="http://localhost/index.php?id=13" />
            '),
            trim($response->getContent())
        );
    }

    /**
     * Page "Schweiz-4" is not connected
     *
     * @test
     */
    public function schweiz4PageOutput()
    {

        $response = $this->getFrontendResponse(18);
        $this->assertEquals(
            trim('
<link rel="alternate" hreflang="de-ch" href="http://localhost/index.php?id=18&foo=bar" />
<link rel="alternate" hreflang="fr-ch" href="http://localhost/index.php?id=18&L=2&foo=bar&john=doe" />
            '),
            trim($response->getContent())
        );
    }
}
