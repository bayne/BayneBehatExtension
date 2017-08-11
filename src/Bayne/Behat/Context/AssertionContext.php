<?php

namespace Bayne\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Mink\Exception\ElementHtmlException;
use Behat\MinkExtension\Context\MinkContext;

class AssertionContext implements Context
{
    /**
     * @var MinkContext
     */
    protected $minkContext;

    /**
     * @var string
     */
    protected $buildPath;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function setUpTestEnvironment($scope)
    {
        $environment = $scope->getEnvironment();
        if ($environment instanceof InitializedContextEnvironment) {
            $this->minkContext = $environment->getContext(MinkContext::class);
        }
    }

    /**
     * @Then /^the "([^"]*)" field should be required$/
     */
    public function theFieldShouldBeRequired($field)
    {
        $element = $this->minkContext->getSession()->getPage()->findField($field);
        if (false === $element->hasAttribute('required')) {
            throw new ElementHtmlException('Field is not marked as required', $this->minkContext->getSession()->getDriver(), $element);
        }
    }

    public function spinWait(callable $checkFunction, $limit = 5000)
    {
        $pollFrequency = 200000;
        $time = 0;
        while (!$checkFunction()) {
            usleep($pollFrequency);
            $time += ($pollFrequency/1000);

            if ($time >= $limit) {
                break;
            }
        }
    }

    /**
     * @Then /^I should see a log entry with message "([^"]*)"$/
     */
    public function iShouldSeeALogEntryWithMessage($message)
    {
    }

    /**
     * @return string
     */
    public function getBuildPath()
    {
        return $this->buildPath;
    }

    /**
     * @param string $buildPath
     *
     * @return AssertionContext
     */
    public function setBuildPath($buildPath)
    {
        $this->buildPath = $buildPath;

        return $this;
    }

}
