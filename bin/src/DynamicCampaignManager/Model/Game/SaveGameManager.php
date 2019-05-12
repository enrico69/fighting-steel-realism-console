<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/05/2019 (dd-mm-YYYY)
 */
namespace App\DynamicCampaignManager\Model\Game;

class SaveGameManager
{
    /**
     * @var \App\DynamicCampaignManager\Model\Game\SaveGameFileProcessor
     */
    private $saveGameFileProcessor;

    /**
     * SaveGameManager constructor.
     * @param \App\DynamicCampaignManager\Model\Game\SaveGameFileProcessor $saveGameFileProcessor
     */
    public function __construct(SaveGameFileProcessor $saveGameFileProcessor)
    {
        $this->saveGameFileProcessor = $saveGameFileProcessor;
    }

    /**
     * @param string $key
     * @throws \RuntimeException
     */
    public function deleteSaveGame(string $key) : void
    {
        // SET EMPTYING THE FOLDER IN THE FILE PROCESSOR
        $scenarioSaveDir = $this->saveGameFileProcessor->getScenarioDir($key);
        if (file_exists($scenarioSaveDir)) {
            // HERE ALSO DELETE THE FILES IN THE FOLDER BEFORE DELETING THE FOLDER ITSELF
            $result = @rmdir($scenarioSaveDir);
            if (!$result && file_exists($scenarioSaveDir)) {
                throw new \RuntimeException(
                    "An error occured during the deletion the old savegame for the scenario $key"
                );
            }
        }
    }

    /**
     * @param string $key
     * @throws \RuntimeException
     */
    public function createSaveGame(string $key) : void
    {
        $scenarioSaveDir = $this->saveGameFileProcessor->getScenarioDir($key);
        $result = @mkdir($scenarioSaveDir);
        if (!$result) {
            throw new \RuntimeException(
                "An error occured during the creation of a new game for the scenario $key"
            );
        }

        // HERE CREATE THE BASIC FILE. With the color for instance...
    }
}
