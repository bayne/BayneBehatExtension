<?php

namespace Bayne\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\MinkExtension\Context\MinkContext;

class ScreenshotContext implements Context
{
    /**
     * @var ScenarioInterface
     */
    private $currentScenario;
    /**
     * @var MinkContext
     */
    private $minkContext;
    private $manualScreenshotPath;
    private $screenshotPath;

    public function setScreenshotPath($screenshotPath)
    {
        $this->screenshotPath = $screenshotPath;
    }

    public function setManualScreenshotPath($manualScreenshotPath)
    {
        $this->manualScreenshotPath = $manualScreenshotPath;
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function setUpTestEnvironment($scope)
    {
        $this->currentScenario = $scope->getScenario();
        $environment = $scope->getEnvironment();
        if ($environment instanceof InitializedContextEnvironment) {
            $this->minkContext = $environment->getContext(MinkContext::class);
        }
    }

    /**
     * @Then /^it displays correctly$/
     */
    public function itDisplaysCorrectly()
    {
        $hash = md5($this->currentScenario->getTitle());
        //create filename string

        $filename = $hash.'.png';
        $screenshotsDir = $this->getContainer()->getParameter('kernel.root_dir').'/../build/behat/manual/screenshots/';

        //create screenshots directory if it doesn't exist
        if (!file_exists($screenshotsDir)) {
            mkdir($screenshotsDir, 0777, true);
        }

        //take screenshot and save as the previously defined filename
        file_put_contents($screenshotsDir . '/' . $filename, $this->minkContext->getSession()->getDriver()->getScreenshot());
    }
}
