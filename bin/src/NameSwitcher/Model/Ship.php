<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-26
 */
namespace App\NameSwitcher\Model;

use App\NameSwitcher\Exception\NoShipException;

/**
 * Class Ship
 */
class Ship
{
    public const SHORT_NAME_MAX_LENGTH = 10;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $tasName = '';

    /**
     * @var string
     */
    protected $fsName = '';

    /**
     * @var array
     */
    protected $similarTo = [];

    /**
     * int qty of fields expected in the dictionary
     */
    public const FIELD_QTY = 5;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Ship
     */
    public function setType(string $type): Ship
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return Ship
     */
    public function setClass(string $class): Ship
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getTasName(): string
    {
        return $this->tasName;
    }

    /**
     * @param string $tasName
     *
     * @return Ship
     */
    public function setTasName(string $tasName): Ship
    {
        $this->tasName = $tasName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFsName(): string
    {
        return $this->fsName;
    }

    /**
     * @param string $fsName
     *
     * @return Ship
     */
    public function setFsName(string $fsName): Ship
    {
        $this->fsName = $fsName;

        return $this;
    }

    /**
     * @return array
     */
    public function getSimilarTo(): array
    {
        return $this->similarTo;
    }

    /**
     * @param string $similarTo
     *
     * @return Ship
     */
    public function setSimilarTo(string $similarTo): Ship
    {
        $similarShips = explode(
            ',',
            $similarTo
        );

        $ships = [];
        foreach ($similarShips as $shipString) {
            $ships[] = $shipString;
        }
        $this->similarTo = $ships;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return Ship
     */
    public function hydrate(array $data) : Ship
    {
        foreach ($data as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }

        return $this;
    }

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function matchCriteria(array $criteria) : bool
    {
        $match = true;

        foreach ($criteria as $ruleName => $ruleValue) {
            $methodName = 'get' . ucfirst($ruleName);
            if (!method_exists($this, $methodName)
                || $this->$methodName() !== $ruleValue
            ) {
                $match = false;
                break;
            }
        }

        return $match;
    }

    /**
     * @return string
     *
     * @throws NoShipException
     * @throws \Exception
     */
    public function getRandomSimilarShip() : string
    {
        $count = count($this->similarTo);
        if ($count === 0) {
            throw new NoShipException(
                'No similar ship found for' . $this->getType() . ' ' . $this->getTasName()
            );
        }

        return $this->similarTo[random_int(0, $count - 1)];
    }
}
