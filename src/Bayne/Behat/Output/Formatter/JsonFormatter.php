<?php

namespace Bayne\Behat\Output\Formatter;

use Bayne\Behat\Output\Printer\OutputHtmlPrinter;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Vanare\BehatCucumberJsonFormatter\Formatter\Formatter;
use Vanare\BehatCucumberJsonFormatter\Node;

class JsonFormatter extends Formatter
{
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

    public function __construct($filename, $outputDir, $profilerDir)
    {
        parent::__construct($filename, $outputDir);
        $this->profilerDir = $profilerDir;
        $this->filename = $filename;
        $this->outputDir = $outputDir;
    }

    public static function getEmbeddingId($featureFilename, $stepLineNumber)
    {
        return md5($featureFilename.'|'.$stepLineNumber);
    }

    protected function processStep(Node\Step $step, TestResult $result)
    {
        $id = self::getEmbeddingId($this->getCurrentFeature()->getFile(), $step->getLine());
        $embeddings = [];
        if (is_dir($this->profilerDir.'/'.$id)) {
            $embeddings['profiler'] = $id;
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
        parent::onAfterExercise($event);
        $contents = file_get_contents(__DIR__.'/../output.html');
        $file = $this->outputDir.'/output.html';
        file_put_contents($file, $contents);
    }
}
