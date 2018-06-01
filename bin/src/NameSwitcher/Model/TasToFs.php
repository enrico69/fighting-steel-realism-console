<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-27
 */
namespace App\NameSwitcher\Model;

use App\NameSwitcher\Model\Dictionary;

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

    /**
     * @var array
     */
    protected $classCount = [];

    /**
     * @var \App\NameSwitcher\Model\Dictionary
     */
    protected $dictionaryProcessor;

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
        $this->dictionaryProcessor = new Dictionary($this->getDictionaryFilepath());

        if (!in_array($obfuscateLevel, self::AUTHORIZED_SWITCH_LEVEL)) {
            throw new \LogicException('Unknown switching level');
        }
        $this->obfuscatingLevel = $obfuscateLevel;
    }

    public function processScenario() : void
    {

        $fileName = 'toto.csv';



        // backup scenar
        // load in memory



        $tasShipName = 'Clemenceau';
        $shipType    = 'BB';

        $criteria = [
            'name' => $tasShipName,
            'type' => $shipType,
        ];
        $newShipName = $this->getReplacementShipName($criteria);


        // write in file
    }

    /**
     * @TODO : a next release, allow to select the dictionnary
     * from a list created from a given directory. The choice
     * will be injected in the constructor, and the current method
     * will be removed.
     *
     * @return string
     */
    protected function getDictionaryFilepath() : string
    {
        return __DIR__ . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'dictionary.csv';
    }

    /**
     * @param array $criteria
     *
     * @return string
     *
     * @throws \LogicException
     * @throws \Exception
     */
    protected function getReplacementShipName(array $criteria) : string
    {
        $replacingShip = $this->dictionaryProcessor->findOneShip($criteria);
        $this->updateClassCount($replacingShip->getClass());

        if ($this->obfuscatingLevel === self::SWITCH_LEVEL_BASIC) {
            $newShipName = $replacingShip->getFsName();
        } elseif ($this->obfuscatingLevel === self::SWITCH_LEVEL_OBFUSCATE) {
            $newShipName = $replacingShip->getClass() . $this->classCount[$replacingShip->getClass()];
        } elseif ($this->obfuscatingLevel === self::SWITCH_LEVEL_OBFUSCATE_CONFUSED) {
            $similarTo = $replacingShip->getRandomSimilarShip();
            $this->updateClassCount($similarTo['class']);
            $newShipName = $similarTo['class'] . $this->classCount[$similarTo['class']];
        } else {
            // Should never happen as it is already checked in the constructor
            throw new \LogicException('Unknown obfuscating level: ' . $this->obfuscatingLevel);
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
}
