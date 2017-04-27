<?php

namespace Bayne\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ElementHtmlException;
use Behat\MinkExtension\Context\MinkContext;

class AssertionContext implements Context
{
    /**
     * @var MinkContext
     */
    protected $minkContext;

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
}
