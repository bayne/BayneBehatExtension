<?php


namespace Bayne\Behat;


use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepNode;

trait ScreenshotFilenameTrait
{

    public function getScreenshotFilename(ScenarioInterface $scenario)
    {
        $steps = array_map(
            function (StepNode $stepNode) {
                return $stepNode->getText();
            },
            $scenario->getSteps()
        );

        $steps = array_reduce(
            $steps,
            function ($stepNodeA, $stepNodeB) {
                return $stepNodeA.$stepNodeB;
            }
        );

        $hash = md5($scenario->getTitle().$steps);

        return $hash.'.png';
    }

}
