<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-27
 */
namespace App\NameSwitcher\Model\Dictionary;

use App\NameSwitcher\Model\Dictionary\Report;
use App\NameSwitcher\Model\Dictionary;
use App\Core\Model\File;
use App\NameSwitcher\Model\Dictionary\Validator as DictionaryValidator;
use App\Core\Model\Directory;
use App\NameSwitcher\Model\Dictionary\Reader as DictionaryReader;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Exception\MoreThanOneShipException;

/**
 * Class ScenarioValidator
 * @package App\NameSwitcher\Model\Dictionary
 */
class ScenarioValidator
{
    /**
     * @var string
     */
    protected static $scenarioPath = '';

    /**
     * @var \App\NameSwitcher\Model\Dictionary
     */
    protected static $dictionary;

    /**
     * @param string $scenarioDirName
     * @param string $fsShipFileName
     * @param bool   $issueReport
     * @return array
     *
     * @throws \Exception
     */
    public static function validate(
        string $scenarioDirName,
        string $fsShipFileName,
        bool $issueReport = false
    ) : array {
        // First validate the dictionary itself
        DictionaryReader::checkFilePresence();
        $report = DictionaryValidator::validateStandardDictionary($issueReport);

        if (empty($report)) {
            // If validation is OK, check the dictionary against the scenario.
            static::hydrateDictionary();

            // First : extract the TAS ship list
            static::$scenarioPath = Directory::getTasScenarioDirectory()
                . $scenarioDirName
                . DIRECTORY_SEPARATOR;

            $shipsList           = [];
            $shipsList['allied'] = static::readShipsList('Allied');
            $shipsList['axis']   = static::readShipsList('Axis');

            // Validate the TAS files (axis and allied)
            static::validateShipsList($shipsList, $report, 'TAS');

            // Next, extract the ships from the FS ship list
            $shipsList = [];
            $shipsList['N/A'] = static::readFsShipsList($fsShipFileName);
            static::validateShipsList($shipsList, $report, 'FS');
        }

        if ($issueReport && !empty($report)) {
            Report::issueReport($report);
        }

        return $report;
    }

    /**
     * Hydrate the dictionary
     */
    protected static function hydrateDictionary() : void
    {
        static::$dictionary = new Dictionary(
            DictionaryReader::readFile(
                DictionaryReader::getDictionaryPath()
            )
        );
    }

    /**
     * @param array  $shipsList
     * @param array  $report
     * @param string $context
     *
     * @throws \Exception
     */
    protected static function validateShipsList(
        array $shipsList,
        array &$report,
        string $context
    ) : void {
        $uniqueShipsName = [];
        foreach ($shipsList as $side => $ships) {
            foreach ($ships as $lineNumber => $line) {
                if (strpos($line, 'NAME=') !== false) {
                    if (strpos($line, 'SHORTNAME=') !== false) {
                        continue;
                    }
                    $data     = explode('=', $line);
                    $shipName = trim($data[1]);
                    if (in_array($shipName, $uniqueShipsName)) {
                        $msg = "$side $context file: the ship $shipName"
                            . ' is present more than once';
                        $report[] = Report::getReportElement(
                            Report::STRING_GRAVITY_NOTICE,
                            $lineNumber,
                            $msg
                        );
                    }
                    $uniqueShipsName[] = $shipName;

                    try {
                        static::$dictionary->findOneShip(['tasName' => $shipName]);
                    } catch (NoShipException $ex) {
                        $msg = "$side $context file: the ship $shipName"
                            . ' is not present in the dictionary.';
                        $report[] = Report::getReportElement(
                            Report::STRING_GRAVITY_ERROR,
                            $lineNumber,
                            $msg
                        );
                    } catch (MoreThanOneShipException $e) {
                        $msg = "$side $context file: the ship $shipName"
                            . ' is present more than once in the dictionary.';
                        $report[] = Report::getReportElement(
                            Report::STRING_GRAVITY_NOTICE,
                            $lineNumber,
                            $msg
                        );
                    }
                }
            }
        }
    }

    /**
     * @param string $fsShipFileName
     *
     * @return array
     *
     * @throws \LogicException
     */
    protected static function readFsShipsList(string $fsShipFileName) : array
    {
        return File::readTextFileContent(
            static::$scenarioPath . "$fsShipFileName",
            'FS ships file'
        );
    }

    /**
     * @param string $side
     *
     * @return array
     *
     * @throws \LogicException
     */
    protected static function readShipsList(string $side) : array
    {
        return File::readTextFileContent(
            static::$scenarioPath . "{$side}Ships.cfg",
            "$side ships file"
        );
    }
}
