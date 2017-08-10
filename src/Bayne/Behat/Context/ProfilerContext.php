<?php

namespace Bayne\Behat\Context;

use Bayne\Behat\Output\Formatter\JsonFormatter;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\DriverException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

class ProfilerContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @var MinkContext
     */
    private $minkContext;
    /**
     * @var AssertionContext
     */
    private $assertionContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     *
     * @throws ContextNotFoundException
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext(MinkContext::class);
        $this->assertionContext = $environment->getContext(AssertionContext::class);
    }

    /**
     * @AfterStep
     *
     * @param AfterStepScope $scope
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function afterStepDebuggerToken(AfterStepScope $scope)
    {
        $session = $this->minkContext->getSession();
        try {
            $token = $session->getCookie('bayne.symfony_web_profiler_html_bundle.x_debug_token');
            $this
                ->getContainer()
                ->get('logger')
                ->notice(
                    'Debug token',
                    [
                        'title' => $scope->getStep()->getText(),
                        'token' => $token,
                    ]
                )
            ;

            $id = JsonFormatter::getEmbeddingId($scope->getFeature()->getFile(), $scope->getStep()->getLine());
            if ($this->getContainer()->has('bayne.symfony_web_profiler_html_bundle.outputter')) {
                $this->getContainer()->get('bayne.symfony_web_profiler_html_bundle.outputter')->write(
                    $token,
                    $this->assertionContext->getBuildPath().'/profiler/'.$id
                );
            }

        } catch (DriverException $exception) {
            $this
                ->getContainer()
                ->get('logger')
                ->notice(
                    'Driver exception thrown',
                    [
                        'exception' => $exception->getMessage(),
                    ]
                )
            ;
        }
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $beforeScenarioScope
     */
    public function beforeScenarioLogBegin(BeforeScenarioScope $beforeScenarioScope)
    {
        $this->getContainer()->get('logger')->notice('Begin scenario', ['title' => $beforeScenarioScope->getScenario()->getTitle()]);
    }

    /**
     * @AfterScenario
     *
     * @param AfterScenarioScope $afterScenarioScope
     */
    public function afterScenarioLogBegin(AfterScenarioScope $afterScenarioScope)
    {
        $this->getContainer()->get('logger')->notice('End scenario', ['title' => $afterScenarioScope->getScenario()->getTitle()]);
    }
}
