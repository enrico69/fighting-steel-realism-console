<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-09
 */
namespace App\Core\Model;

/**
 * Class Directory
 * @package App\Core\Model
 */
class Directory
{
    /**
     * @return string
     */
    public static function getRootPath(): string
    {
        return
            __DIR__ . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public static function getScenarioDirectory() : string
    {
        return Configuration::getConfigurationFileContent()['FS-LOCATION']
            . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public static function getTasScenarioDirectory() : string
    {
        return Configuration::getConfigurationFileContent()['TAS-LOCATION']
            . DIRECTORY_SEPARATOR;
    }
}
