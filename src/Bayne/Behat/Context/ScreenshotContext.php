<?php

namespace Bayne\Behat\Context;

use Bayne\Behat\ScreenshotFilenameTrait;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;

class ScreenshotContext implements Context
{
    use ScreenshotFilenameTrait;
    /**
     * @var ScenarioInterface
     */
    private $currentScenario;
    /**
     * @var MinkContext
     */
    private $minkContext;
    /**
     * @var string
     */
    private $manualScreenshotPath;
    /**
     * @var string
     */
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
     * @AfterStep
     *
     * @param AfterStepScope $scope
     */
    public function afterStep(AfterStepScope $scope)
    {
        //if test has failed, and is not an api test, get screenshot
        if(!$scope->getTestResult()->isPassed() && $this->minkContext->getSession()->getDriver() instanceof Selenium2Driver)
        {
            //create filename string

            $featureFolder = preg_replace('/\W/', '', $scope->getFeature()->getTitle());

            $scenarioName = $this->currentScenario->getTitle();
            $fileName = preg_replace('/\W/', '', $scenarioName) . '.png';

            //create screenshots directory if it doesn't exist
            if (!file_exists($this->screenshotPath. $featureFolder)) {
                mkdir($this->screenshotPath . $featureFolder, 0777, true);
            }

            //take screenshot and save as the previously defined filename
            file_put_contents($this->screenshotPath . $featureFolder . '/' . $fileName, $this->minkContext->getSession()->getDriver()->getScreenshot());
        }

    }

    /**
     * @Then /^it displays correctly$/
     */
    public function itDisplaysCorrectly()
    {
        if (false === ($this->minkContext->getSession()->getDriver() instanceof Selenium2Driver)) {
            throw new UnsupportedDriverActionException(
                'Screenshots are not supported in the supplied driver',
                $this->minkContext->getSession()->getDriver()
            );
        }

        $filename = $this->getScreenshotFilename($this->currentScenario);

        //create screenshots directory if it doesn't exist
        if (!file_exists($this->manualScreenshotPath)) {
            mkdir($this->manualScreenshotPath, 0777, true);
        }

        //take screenshot and save as the previously defined filename
        file_put_contents($this->manualScreenshotPath . '/' . $filename, $this->minkContext->getSession()->getDriver()->getScreenshot());
    }
}
