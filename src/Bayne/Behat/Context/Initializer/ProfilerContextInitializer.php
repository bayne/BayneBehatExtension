<?php

namespace Bayne\Behat\Context\Initializer;


use Bayne\Behat\Context\ProfilerContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

class ProfilerContextInitializer implements ContextInitializer
{
    /**
     * @var string
     */
    private $manualTagName;

    /**
     * ProfilerContextInitializer constructor.
     *
     * @param string $manualTagName
     */
    public function __construct($manualTagName)
    {
        $this->manualTagName = $manualTagName;
    }


    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof ProfilerContext) {
            $context->setManualTagName($this->manualTagName);
        }
    }
}
