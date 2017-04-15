<?php

namespace Bayne\Behat\Output\Formatter;

use Bayne\Behat\ScreenshotFilenameTrait;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Factory\FilesystemOutputFactory;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Output\Printer\StreamOutputPrinter;

class ManualScreenshotFormatter implements Formatter
{
    use ScreenshotFilenameTrait;
    /**
     * @var FilesystemOutputFactory
     */
    private $outputFactory;
    /**
     * @var OutputPrinter
     */
    private $outputPrinter;
    /**
     * @var string
     */
    private $tagname;

    /**
     * ManualScreenshotFormatter constructor.
     *
     * @param $filename
     * @param $path
     * @param $tagname
     */
    public function __construct (
        $filename,
        $path,
        $tagname
    ) {
        $this->outputFactory = new FilesystemOutputFactory();
        $this->outputFactory->setFileName($filename);
        $this->outputFactory->setOutputPath($path);
        $this->outputPrinter = new StreamOutputPrinter($this->outputFactory);
        $this->tagname = $tagname;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'tester.scenario_tested.after'     => 'onAfterScenarioTested'
        );
    }

    public function onAfterScenarioTested(AfterScenarioTested $event)
    {
        if (in_array($this->tagname, $event->getScenario()->getTags())) {
            $steps = array_map(
                function (StepNode $stepNode) {
                    return $stepNode->getKeyword().' '.$stepNode->getText();
                },
                $event->getScenario()->getSteps()
            );
            $this->getOutputPrinter()->writeln('```');
            $this->getOutputPrinter()->writeln('Scenario: '.$event->getScenario()->getTitle());
            $this->getOutputPrinter()->writeln($steps);
            $this->getOutputPrinter()->writeln('```');
            $this->getOutputPrinter()->writeln(
                sprintf(
                    '![](screenshots/%s)',
                    $this->getScreenshotFilename($event->getScenario())
                )
            );
        }
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'manual';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Returns formatter output printer.
     *
     * @return OutputPrinter
     */
    public function getOutputPrinter()
    {
        return $this->outputPrinter;
    }

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
        // TODO: Implement setParameter() method.
    }

    /**
     * Returns parameter name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        // TODO: Implement getParameter() method.
    }
}
