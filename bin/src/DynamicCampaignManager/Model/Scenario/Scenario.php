<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/05/2019 (dd-mm-YYYY)
 */
namespace App\DynamicCampaignManager\Model\Scenario;

class Scenario
{
    public const FILE_INFO_TITLE = '[TITLE]';
    public const FILE_INFO_SHORT_DESC = '[SHORT DESCRIPTION]';
    public const FILE_INFO_FULL_DESC = '[FULL DESC]';

    public const INFO_KEYS = [
        self::FILE_INFO_TITLE,
        self::FILE_INFO_SHORT_DESC,
        self::FILE_INFO_FULL_DESC,
    ];

    private $key;
    private $title;
    private $shortDescription;
    private $fullDescription;

    /**
     * @param string $scenarioKey
     * @param array  $data
     *
     * @throws \LogicException
     */
    public function hydrateFromInfo(string $scenarioKey, array $data) : void
    {
        foreach (self::INFO_KEYS as $key) {
            if (!\array_key_exists($key, $data)) {
                throw new \LogicException(
                    "Invalid scenario info file for scenario '{$scenarioKey}'. The key '{$key}' is missing."
                );
            }
        }

        $this->key = $scenarioKey;
        $this->fullDescription = $data[self::FILE_INFO_FULL_DESC];
        $this->shortDescription = $data[self::FILE_INFO_SHORT_DESC];
        $this->title = $data[self::FILE_INFO_TITLE];
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getShortDescription() : string
    {
        return $this->shortDescription;
    }
}
