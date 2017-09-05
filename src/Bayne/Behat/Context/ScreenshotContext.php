<?php

namespace Bayne\Behat\Context;

use Bayne\Behat\Output\Formatter\JsonFormatter;
use Bayne\Behat\ScreenshotFilenameTrait;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Gherkin\Node\ScenarioInterface;
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
    /**
     * @var string
     */
    private $manualTagName;
    /**
     * @var string
     */
    private $featureFile;
    /**
     * @var string
     */
    private $stepLineNumber;

    /**
     * @param string $manualTagName
     *
     * @return ScreenshotContext
     */
    public function setManualTagName($manualTagName)
    {
        $this->manualTagName = $manualTagName;

        return $this;
    }

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
        $this->featureFile = $scope->getFeature()->getFile();
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
        if ($this->minkContext->getSession()->getDriver() instanceof Selenium2Driver) {

            if (!$scope->getTestResult()->isPassed() || $this->currentScenario->hasTag($this->manualTagName)) {
                //create filename string

                $embeddingId = JsonFormatter::getEmbeddingId($this->featureFile, $scope->getStep()->getLine());

                $filename = $this->getScreenshotFilename($this->currentScenario);

        //create screenshots directory if it doesn't exist
                if (!file_exists($this->manualScreenshotPath)) {
                    mkdir($this->manualScreenshotPath, 0777, true);
                }

                //take screenshot and save as the previously defined filename
                $screenshotData = $this->minkContext->getSession()->getDriver()->getScreenshot();
                file_put_contents($this->manualScreenshotPath.'/'.$filename, $screenshotData);
                file_put_contents($this->manualScreenshotPath.'/'.$embeddingId.'.png', $screenshotData);
            }
        }

    }


    /**
     * @BeforeStep
     */
    public function stepLineNumber(BeforeStepScope $scope)
    {
        $this->stepLineNumber = $scope->getStep()->getLine();
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

        $embeddingId = JsonFormatter::getEmbeddingId($this->featureFile, $this->stepLineNumber);

        $filename = $this->getScreenshotFilename($this->currentScenario);

        //create screenshots directory if it doesn't exist
        if (!file_exists($this->manualScreenshotPath)) {
            mkdir($this->manualScreenshotPath, 0777, true);
        }

        //take screenshot and save as the previously defined filename
        $screenshotData = $this->minkContext->getSession()->getDriver()->getScreenshot();
        file_put_contents($this->manualScreenshotPath.'/'.$filename, $screenshotData);
        file_put_contents($this->manualScreenshotPath.'/'.$embeddingId.'.png', $screenshotData);
    }
}
