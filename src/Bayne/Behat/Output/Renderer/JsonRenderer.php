<?php

namespace Bayne\Behat\Output\Renderer;

use Vanare\BehatCucumberJsonFormatter\Renderer\JsonRenderer as BaseJsonRenderer;

class JsonRenderer extends BaseJsonRenderer
{

    /**
     * @param bool|true $asString
     *
     * @return array|string
     */
    public function getResult($asString = true)
    {
        if ($asString) {
            $mergedResultArray= [];

            foreach ($this->result as $suiteResultItem) {
                $mergedResultArray = array_merge($mergedResultArray, $suiteResultItem);
            }

            return json_encode($mergedResultArray);
        }

        return $this->result;
    }
}
