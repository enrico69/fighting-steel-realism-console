<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-27
 */
namespace App\NameSwitcher\Model;

use App\Core\Model\Configuration;
use App\NameSwitcher\Model\Dictionary\Reader as DictionaryReader;

/**
 * Class TasToFs
 * @package App\NameSwitcher\Model
 */
class TasToFs
{
    public const SWITCH_LEVEL_BASIC              = 'switch';
    public const SWITCH_LEVEL_OBFUSCATE          = 'switch_with_obfuscate';
    public const SWITCH_LEVEL_OBFUSCATE_CONFUSED = 'switch_with_obfuscate_confused';

    private const AUTHORIZED_SWITCH_LEVEL = [
        self::SWITCH_LEVEL_BASIC,
        self::SWITCH_LEVEL_OBFUSCATE,
        self::SWITCH_LEVEL_OBFUSCATE_CONFUSED,
    ];

    public const SCENARIO_FILENAME      = 'A_TAS_Scenario.scn';
    public const SCENARIO_SAVE_FILENAME = 'A_TAS_Scenario.scn.bak';
    public const SCENARIO_REVERT_FILE   = 'A_TAS_RevertDictionary.txt';

    /**
     * @var array
     */
    protected $classCount = [];

    /**
     * @var \App\NameSwitcher\Model\Dictionary
     */
    protected $dictionary;

    /**
     * @var string
     */
    protected $obfuscatingLevel = '';

    /**
     * TasToFs constructor.
     * @param string $obfuscateLevel
     */
    public function __construct(string $obfuscateLevel)
    {
        DictionaryReader::checkFilePresence();
        $this->dictionary = new Dictionary(
            DictionaryReader::readFile(
                DictionaryReader::getDictionaryPath()
            )
        );

        if (!in_array($obfuscateLevel, self::AUTHORIZED_SWITCH_LEVEL)) {
            throw new \LogicException('Unknown switching level');
        }
        $this->obfuscatingLevel = $obfuscateLevel;
    }

    /**
     * @return string
     */
    public function getScenarioFullPath() : string
    {
        return $this->getScenarioDirectory() . self::SCENARIO_FILENAME;
    }

    /**
     * @return string
     */
    public function getScenarioCopyFullPath() : string
    {
        return $this->getScenarioDirectory() . self::SCENARIO_SAVE_FILENAME;
    }

    /**
     * @return string
     */
    public function getScenarioRevertDictionaryFullPath() : string
    {
        return $this->getScenarioDirectory() . self::SCENARIO_REVERT_FILE;
    }

    /**
     * @throws \App\NameSwitcher\Exception\MoreThanOneShipException
     * @throws \App\NameSwitcher\Exception\NoShipException
     * @throws \Exception
     *
     * @return void
     */
    public function processScenario() : void
    {
        $this->deleteBackup();
        $this->makeScenarioCopy();
        $scenarioContent    = $this->readScenarioContent();
        $scenarioRevertData = [];

        foreach ($scenarioContent as &$line) {
            if (strpos($line, 'NAME=') === 0) {
                //echo "Ligne: $line" . PHP_EOL;
                $shipName = substr($line, 5);
                //echo "Nom TAS: $shipName" . PHP_EOL;
                $newName = $this->getReplacementShipName(
                    ['tasName' => $shipName]
                );
                $line                 = 'NAME=' . $newName;
                $scenarioRevertData[] = $newName . '|' . $shipName . PHP_EOL;
                //echo "Nom FS: $newName" . PHP_EOL;
            }
            $line .= PHP_EOL;
        }
        unset($line);

        $this->outputNewScenarioContent($scenarioContent);
        $this->outputRevertDictionary($scenarioRevertData);
    }

    /**
     * @param array $criteria
     *
     * @return string
     *
     * @throws \App\NameSwitcher\Exception\MoreThanOneShipException
     * @throws \App\NameSwitcher\Exception\NoShipException
     * @throws \Exception
     */
    protected function getReplacementShipName(array $criteria) : string
    {
        $replacingShip = $this->dictionary->findOneShip($criteria);
        $this->updateClassCount($replacingShip->getClass());

        if ($this->obfuscatingLevel === self::SWITCH_LEVEL_BASIC) {
            $newShipName = $replacingShip->getFsName();
        } elseif ($this->obfuscatingLevel === self::SWITCH_LEVEL_OBFUSCATE) {
            $newShipName = $replacingShip->getClass()
                . $this->classCount[$replacingShip->getClass()];
        } elseif ($this->obfuscatingLevel === self::SWITCH_LEVEL_OBFUSCATE_CONFUSED) {
            $similarTo = $replacingShip->getRandomSimilarShip();
            $this->updateClassCount($similarTo);
            $newShipName = $similarTo
                . $this->classCount[$similarTo];
        } else {
            // Should never happen as it is already checked in the constructor
            throw new \LogicException(
                'Unknown obfuscating level: ' . $this->obfuscatingLevel
            );
        }

        return $newShipName;
    }

    /**
     * @param string $class
     */
    protected function updateClassCount(string $class) : void
    {
        if (array_key_exists($class, $this->classCount)) {
            $this->classCount[$class]++;
        } else {
            $this->classCount[$class] = 1;
        }
    }

    /**
     * @return string
     */
    protected function getScenarioDirectory() : string
    {
        return $scenarioPath = Configuration::getConfigurationFileContent()['FS-LOCATION']
            . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return void
     *
     * @throws \LogicException
     */
    protected function makeScenarioCopy() : void
    {
        $status = rename(
            $this->getScenarioFullPath(),
            $this->getScenarioCopyFullPath()
        );

        if (!$status) {
            throw new \LogicException('Impossible to make a copy of the scenario.');
        }
    }

    /**
     * @return array
     *
     * @throws \LogicException
     */
    protected function readScenarioContent() : array
    {
        $content = [];
        $handle  = @fopen($this->getScenarioCopyFullPath(), 'r');

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $content[] = trim($buffer);
            }
            fclose($handle);
        } else {
            throw new \LogicException('Impossible to read the content of the scenario.');
        }

        return $content;
    }

    /**
     * @return void
     *
     * @throws \LogicException
     */
    protected function deleteBackup() : void
    {
        /**
         * The file 'A_TAS_Scenario.scn' is supposed to be manually overrided
         * by the user. But try to delete all previously generated files.
         */

        $filesToDelete = [
          $this->getScenarioCopyFullPath() => 'a previous scenario backup.',
          $this->getScenarioRevertDictionaryFullPath() => 'a previous scenario revert dictionary.',
        ];

        foreach ($filesToDelete as $filePath => $errorMsg) {
            if (file_exists($filePath)) {
                $delete = unlink($filePath);
                if (!$delete) {
                    throw new \LogicException("Impossible to delete $errorMsg");
                }
            }
        }
    }

    /**
     * @param array $scenarioContent
     *
     * @throws \LogicException
     */
    protected function outputNewScenarioContent(array $scenarioContent) : void
    {
        $result = file_put_contents(
            $this->getScenarioFullPath(),
            $scenarioContent
        );

        if ($result === false) {
            throw new \LogicException('Impossible to output the new scenario file.');
        }
    }

    /**
     * @param array $data
     *
     * @throws \LogicException
     */
    protected function outputRevertDictionary(array $data) : void
    {
        $result = file_put_contents(
            $this->getScenarioRevertDictionaryFullPath(),
            $data
        );

        if ($result === false) {
            throw new \LogicException('Impossible to output the revert dictionary file.');
        }
    }
}
