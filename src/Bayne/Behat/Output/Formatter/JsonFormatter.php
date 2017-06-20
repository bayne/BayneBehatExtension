<?php

namespace Bayne\Behat\Output\Formatter;

use Behat\Testwork\Tester\Result\TestResult;
use Vanare\BehatCucumberJsonFormatter\Formatter\Formatter;
use Vanare\BehatCucumberJsonFormatter\Node;

class JsonFormatter extends Formatter
{
    /**
     * @var
     */
    private $profilerDir;

    public function __construct($filename, $outputDir, $profilerDir)
    {
        parent::__construct($filename, $outputDir);
        $this->profilerDir = $profilerDir;
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

}
