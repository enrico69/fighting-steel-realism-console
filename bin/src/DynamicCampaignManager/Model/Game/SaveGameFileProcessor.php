<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/05/2019 (dd-mm-YYYY)
 */
namespace App\DynamicCampaignManager\Model\Game;

use App\Core\Model\Directory;

class SaveGameFileProcessor
{
    /**
     * Return the path of the folder of the DCM saves.
     *
     * @return string
     */
    public function getSaveDirectory() : string
    {
        return Directory::getRootPath()
            . 'bin' . DIRECTORY_SEPARATOR . 'save';
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getScenarioDir(string $key) : string
    {
        return $this->getSaveDirectory() . DIRECTORY_SEPARATOR . $key;
    }
}
