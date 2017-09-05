<?php

namespace Bayne\Behat\Output\Formatter;

use Bayne\Behat\Output\Printer\OutputHtmlPrinter;
use Bayne\Behat\Output\Renderer\JsonRenderer;
use Bayne\Behat\ScreenshotFilenameTrait;
use Behat\Behat\EventDispatcher\Event as BehatEvent;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Vanare\BehatCucumberJsonFormatter\Formatter\Formatter;
use Vanare\BehatCucumberJsonFormatter\Node;

class JsonFormatter extends Formatter
{
    use ScreenshotFilenameTrait;
    /**
     * @var string
     */
    private $profilerDir;
    /**
     * @var
     */
    private $filename;
    /**
     * @var
     */
    private $outputDir;
    /**
     * @var JsonRenderer
     */
    private $jsonRenderer;
    /**
     * @var string
     */
    private $manualScreenshotDir;

    public function __construct($filename, $outputDir, $profilerDir, $manualScreenshotDir)
    {
        parent::__construct($filename, $outputDir);
        $this->jsonRenderer = new JsonRenderer($this);
        $this->profilerDir = $profilerDir;
        $this->filename = $filename;
        $this->outputDir = $outputDir;
        $this->manualScreenshotDir = $manualScreenshotDir;
    }

    public static function getEmbeddingId($featureFilename, $stepLineNumber)
    {
        return md5($featureFilename.'|'.$stepLineNumber);
    }

    protected function processStep(Node\Step $step, TestResult $result)
    {
        parent::processStep($step, $result);

        $id = self::getEmbeddingId($this->getCurrentFeature()->getFile(), $step->getLine());
        $embeddings = [];
        if (is_dir($this->profilerDir.'/'.$id)) {
            $embeddings['profiler'] = $id;
        }
        if (is_file($this->manualScreenshotDir.'/'.$id.'.png')) {
            $embeddings['screenshot'] = $id.'.png';
        }
        $step->setEmbeddings($embeddings);
    }

    /**
     * Triggers after running tests.
     *
     * @param AfterExerciseCompleted $event
     */
    public function onAfterExercise(AfterExerciseCompleted $event)
    {
        $this->getTimer()->stop();

        $this->jsonRenderer->render();
        $this->getOutputPrinter()->write($this->jsonRenderer->getResult());

        $contents = file_get_contents(__DIR__.'/../output.html');
        $file = $this->outputDir.'/output.html';
        file_put_contents($file, $contents);
    }
}
