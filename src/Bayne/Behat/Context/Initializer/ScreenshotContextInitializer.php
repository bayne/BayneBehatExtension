<?php

namespace Bayne\Behat\Context\Initializer;

use Bayne\Behat\Context\ScreenshotContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

class ScreenshotContextInitializer implements ContextInitializer
{
    private $manualScreenshotPath;
    private $screenshotPath;

    /**
     * ScreenshotContextInitializer constructor.
     *
     * @param $manualScreenshotPath
     * @param $screenshotPath
     */
    public function __construct($manualScreenshotPath, $screenshotPath)
    {
        $this->manualScreenshotPath = $manualScreenshotPath;
        $this->screenshotPath = $screenshotPath;
    }


    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof ScreenshotContext) {
            $context->setManualScreenshotPath($this->manualScreenshotPath);
            $context->setScreenshotPath($this->screenshotPath);
        }
    }
}
