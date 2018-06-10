<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-03
 */
namespace App\Core\Model;

use App\Core\Model\Directory;

/**
 * Class Configuration
 * @package App\Core\Model
 */
class Configuration
{
    /**
     * @var string configuration file location
     */
    public const CONFIGURATION_FILENAME = 'configuration.cfg';

    /**
     * @var array contains the configuration file content
     */
    protected static $configurationFileContent = [];

    /**
     * @return bool
     */
    public static function configFileExist() : bool
    {
        $status = false;
        if (file_exists(self::getConfigurationFilePath())) {
            $status = true;
        }

        return $status;
    }

    /**
     * @param bool $reload
     *
     * @return array
     *
     * @throws \LogicException
     */
    public static function getConfigurationFileContent(bool $reload = false) : array
    {
        if (!self::configFileExist()) {
            throw new \LogicException('Impossible to find configuration file');
        }

        if ($reload || empty(self::$configurationFileContent)) {
            $readConfigurationFileContent = self::readConfigurationFile();
            self::validateConfigurationData($readConfigurationFileContent);
            self::$configurationFileContent = $readConfigurationFileContent;
        }

        return self::$configurationFileContent;
    }

    /**
     * @return string
     */
    protected static function getConfigurationFilePath() : string
    {
        return Directory::getRootPath() . self::CONFIGURATION_FILENAME;
    }

    /**
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected static function &readConfigurationFile() : array
    {
        $dataRead = [];
        $row      = 1;
        if (($handle = fopen(self::getConfigurationFilePath(), 'r')) !== false) {
            while (!feof($handle)) {
                $data = fgets($handle);
                if (!is_string($data)) {
                    throw new \InvalidArgumentException(
                        'Incorrect format in the configuration file. '
                        . 'Check there is no extra empty line. '
                        . "Line number is : $row"
                    );
                }
                $dataArray = explode('=', $data);

                if (count($dataArray) !== 2) {
                    throw new \InvalidArgumentException(
                        'Incorrect or missing data in the configuration file. '
                        . "Line number is : $row"
                    );
                }
                $dataRead[trim($dataArray[0])] = trim($dataArray[1]);
                $row++;
            }
            fclose($handle);
        } else {
            throw new \LogicException('Impossible to open the configuration file');
        }

        return $dataRead;
    }

    /**
     * @param array $configurationArray
     */
    protected static function validateConfigurationData(array &$configurationArray)
    {
        $dirToCheck = [
            'FS-LOCATION',
            'TAS-LOCATION',
        ];

        foreach ($dirToCheck as $dirName) {
            if (!array_key_exists($dirName, $configurationArray)) {
                throw new \InvalidArgumentException(
                    "Impossible to find the $dirName directory setting."
                );
            }
            if (!is_dir($configurationArray[$dirName])) {
                throw new \InvalidArgumentException(
                    "Impossible to find the $dirName directory."
                    . " Value was: '{$configurationArray[$dirName]}'."
                );
            }
        }
    }
}
