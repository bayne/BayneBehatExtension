<?php

namespace Bayne\Behat\Context\Initializer;


use Bayne\Behat\Context\AssertionContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

class AssertionContextInitializer implements ContextInitializer
{
    /**
     * @var
     */
    private $buildPath;

    /**
     * AssertionContextInitializer constructor.
     *
     * @param $buildPath
     */
    public function __construct($buildPath)
    {
        $this->buildPath = $buildPath;
    }


    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof AssertionContext) {
            $context->setBuildPath($this->buildPath);
        }
    }
}
