<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/05/2019 (dd-mm-YYYY)
 */
namespace App\DynamicCampaignManager\Model\Scenario;

use App\Core\Model\File;
use App\Core\Model\Directory;
use App\DynamicCampaignManager\Log\Logger;

class ScenarioReader
{
    /**
     * @var \App\DynamicCampaignManager\Log\Logger
     */
    private $logger;

    /**
     * ScenarioReader constructor.
     *
     * @param \App\DynamicCampaignManager\Log\Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Scenario[]
     */
    public function getScenarioList() : array
    {
        $scenarios = [];
        $scenariosKeys = Directory::listFolder(self::getScenariosDirectory());
        foreach ($scenariosKeys as $scenarioKey) {
            try {
                $scenario = new Scenario();
                $scenario->hydrateFromInfo($scenarioKey, $this->getScenarioDescription($scenarioKey));
                $scenarios[$scenarioKey] = $scenario;
            } catch (\LogicException $exception) {
            } catch (\Exception $ex) {
                $this->logger->warning(
                    "Error during reading the scenario '{$scenarioKey}'"
                        . ', while generating the scenario list. Error was:' . $ex->getMessage()
                );
            }
        }

        return $scenarios;
    }

    /**
     * @TODO if more keys added, consider refactor
     *
     * Read the description file of a given scenario
     *
     * @param string $scenarioKey
     * @return array
     */
    public function getScenarioDescription(string $scenarioKey) : array
    {
        $data = File::readTextFileContent(
            self::getScenariosDirectory() . DIRECTORY_SEPARATOR
                . $scenarioKey . DIRECTORY_SEPARATOR . 'Description' . DIRECTORY_SEPARATOR . 'info.txt',
            'scenario:' . $scenarioKey
        );

        $result = [];
        $current = '';
        foreach ($data as $line) {
            if (\in_array($line, Scenario::INFO_KEYS)) {
                $current = $line;
                $result[$line] = '';
            } else {
                if (empty($current)) {
                    throw new \LogicException('Invalid scenario info file for scenario ' . $scenarioKey);
                }

                if (!empty($result[$current])) {
                    $result[$current] .= ' ';
                }
                $result[$current] .= $line;
            }
        }

        foreach (Scenario::INFO_KEYS as $key) {
            if (!\array_key_exists($key, $result)) {
                throw new \LogicException("Invalid scenario info file for scenario '{$scenarioKey}'. The key '{$key}' is missing.");
            }
        }

        return $result;
    }

    /**
     * Return the path of the folder of the DCM scenarios.
     *
     * @return string
     */
    private function getScenariosDirectory() : string
    {
        return Directory::getRootPath()
            . 'bin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR
            . 'DcmScenarios';
    }
}