<?php

namespace Bayne\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;

class AccessibilityContext implements Context
{
    /**
     * @var MinkContext
     */
    private $minkContext;


    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext(MinkContext::class);
    }

    /**
     * @Then /^the page should be (Section508) accessible$/
     * @Then /^the page should be (WCAG2AAA) accessible$/
     * @Then /^the page should be (WCAG2AA) accessible$/
     * @Then /^the page should be (WCAG2A) accessible$/
     */
    public function thePageShouldBeAccessible($standard)
    {
        if ($this->minkContext->getSession()->getDriver() instanceof Selenium2Driver) {
            $this->checkAccessibility($standard);
        } else {
            throw new UnsupportedDriverActionException(
                'Requires a javascript session to check for accessibility',
                $this->minkContext->getSession()->getDriver()
            );
        }
    }

    /**
     * @param string $standard
     */
    private function checkAccessibility($standard)
    {
        $injectedJavascript = file_get_contents(__DIR__.'/js/HTMLCS.js');
        $this->minkContext->getSession()->executeScript($injectedJavascript);
        $runnerJS = <<<JS
window.HTMLCS_completed = false;
window.HTMLCS_error = false;
HTMLCS.process(
     '{$standard}',
     document.getElementsByTagName('html')[0].outerHTML,
     function () {
         window.HTMLCS_completed = true;
     },
     function () {
         window.HTMLCS_completed = true;
         window.HTMLCS_error = true;
     }
);
JS;
        $this->minkContext->getSession()->executeScript($runnerJS);
        $this->minkContext->getSession()->getPage()->waitFor(5000, function () {
            return $this->minkContext->getSession()->evaluateScript('window.HTMLCS_completed');
        });
        $processMessagesJS = <<<JS
window.getPath = function (element) {
    let fullPath = [];
    let i = element;
    while (i.parentElement) {
        let path = '';
        i = i.parentElement;
        path += i.tagName;
        if (i.id) {
            path += '#'+i.id;
        } else if (i.className) {
            path += "." + i.className.replace(/ /g, '.');
        }
        fullPath.unshift(path);
    }

    return fullPath.join(' ');
};

window.getErrors = function () {
    let errors = [];
    for (let message of HTMLCS.getMessages()) {
        if (message.type === 1) {
            errors.push({
                msg: message.msg,
                code: message.code,
                type: message.type,
                element: getPath(message.element)
            });
        }
    }

    return JSON.stringify(errors);
};
JS;

        $this->minkContext->getSession()->executeScript($processMessagesJS);
        $errorMessages = json_decode($this->minkContext->getSession()->evaluateScript('window.getErrors()'), true);
        $hasError = $this->minkContext->getSession()->evaluateScript('window.HTMLCS_error');
        if (count($errorMessages) > 0) {
            throw new \PHPUnit_Framework_AssertionFailedError('Accessibility check failed: '.json_encode($errorMessages, JSON_PRETTY_PRINT));
        }
        if ($hasError) {
            throw new \PHPUnit_Framework_AssertionFailedError('Accessibility check failed: There was an error running the accessibility checker');
        }
    }

    /**
     * @Then /^I (should|should not) see an accessible button labeled "([^"]*)"$/
     */
    public function iShouldSeeAnAccessibleButtonLabeled($should, $label)
    {
        $button = $this->getAccessibleButton($label);

        if (null === $button && $should === 'should') {
            throw new \PHPUnit_Framework_AssertionFailedError('Could not find a button with label: '.$label);
        } elseif (null !== $button && $should === 'should not') {
            throw new \PHPUnit_Framework_AssertionFailedError('Found a button with label: '.$label);
        }
    }

    /**
     * @Given /^I press an accessible button labeled "([^"]*)"$/
     */
    public function iPressAnAccessibleButtonLabeld($label)
    {
        $button = $this->getAccessibleButton($label);
        $button->press();
    }

    private function getAccessibleButton($label)
    {
        return $this->minkContext->getSession()->getPage()->find(
            'css',
            'button[aria-label="'.$label.'"],input[aria-label="'.$label.'"]'
        );
    }
}
