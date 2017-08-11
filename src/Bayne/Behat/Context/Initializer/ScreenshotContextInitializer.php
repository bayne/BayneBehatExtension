<?php

namespace Bayne\Behat\Context\Initializer;

use Bayne\Behat\Context\ScreenshotContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

class ScreenshotContextInitializer implements ContextInitializer
{
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
     * ScreenshotContextInitializer constructor.
     *
     * @param string $manualScreenshotPath
     * @param string $screenshotPath
     * @param string $manualTagName
     */
    public function __construct($manualScreenshotPath, $screenshotPath, $manualTagName)
    {
        $this->manualScreenshotPath = $manualScreenshotPath;
        $this->screenshotPath = $screenshotPath;
        $this->manualTagName = $manualTagName;
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
            $context->setManualTagName($this->manualTagName);
        }
    }
}
