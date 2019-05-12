<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/05/2019 (dd-mm-YYYY)
 */
namespace App\DynamicCampaignManager\Model\Scenario;

use App\Core\Model\Directory;
use App\DynamicCampaignManager\Log\Logger;

class ScenarioRepository
{
    /**
     * @var \App\DynamicCampaignManager\Log\Logger
     */
    private $logger;

    /**
     * @var \App\DynamicCampaignManager\Model\Scenario\ScenarioReader
     */
    private $scenarioReader;

    /**
     * ScenarioRepository constructor.
     *
     * @param \App\DynamicCampaignManager\Log\Logger                    $logger
     * @param \App\DynamicCampaignManager\Model\Scenario\ScenarioReader $scenarioReader
     */
    public function __construct(Logger $logger, ScenarioReader $scenarioReader)
    {
        $this->logger = $logger;
        $this->scenarioReader = $scenarioReader;
    }

    /**
     * @return Scenario[]
     */
    public function getScenarioList(): array
    {
        $scenarios     = [];
        $scenariosKeys = Directory::listFolder($this->scenarioReader->getScenariosDirectory());
        foreach ($scenariosKeys as $scenarioKey) {
            try {
                $scenario = new Scenario();
                $scenario->hydrateFromInfo($scenarioKey, $this->scenarioReader->getScenarioDescription($scenarioKey));
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
     * @param string $scenarioKey
     *
     * @return \App\DynamicCampaignManager\Model\Scenario\Scenario
     *
     * @throws \LogicException
     */
    public function getByKey(string $scenarioKey) : Scenario
    {
        $scenario = new Scenario();
        $scenario->hydrateFromInfo($scenarioKey, $this->scenarioReader->getScenarioDescription($scenarioKey));

        return $scenario;
    }
}
