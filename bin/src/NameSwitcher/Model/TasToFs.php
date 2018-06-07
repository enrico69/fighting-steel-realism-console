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
     * @throws \App\NameSwitcher\Exception\MoreThanOneShipException
     * @throws \App\NameSwitcher\Exception\NoShipException
     * @throws \Exception
     *
     * @return void
     */
    public function processScenario() : void
    {
        $this->makeScenarioCopy();
        $scenarioContent = $this->readScenarioContent();

        foreach ($scenarioContent as &$line) {
            if (strpos($line, 'NAME=') === 0) {
                //echo "Ligne: $line" . PHP_EOL;
                $shipName = substr($line, 5);
                //echo "Nom TAS: $shipName" . PHP_EOL;
                $newName = $this->getReplacementShipName(
                    ['name' => $shipName]
                );
                $line = $newName;
                //echo "Nom FS: $newName" . PHP_EOL;
            }
            $line .= PHP_EOL;
        }
        unset($line);

        $this->outputNewScenarioContent($scenarioContent);
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
            $this->updateClassCount($similarTo['class']);
            $newShipName = $similarTo['class']
                . $this->classCount[$similarTo['class']];
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
     * @param array $scenarioContent
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
}
