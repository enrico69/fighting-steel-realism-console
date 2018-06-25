<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-26
 */
namespace App\NameSwitcher\Model;

use App\NameSwitcher\Model\Ship;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Exception\MoreThanOneShipException;
use App\Core\Tools\DisplayTrait;

/**
 * Class Dictionary
 * @package App\NameSwitcher\Model
 */
class Dictionary
{
    /**
     * @var Ship[]
     */
    protected $dictionary = [];

    /**
     * $var string[]
     */
    public const FIELDS_NAME =
        [
            'Type',
            'Class',
            'TasName',
            'FsName',
            'SimilarTo',
        ];

    /**
     * Dictionary constructor.
     *
     * @param array $readRawData
     */
    public function __construct(array $readRawData)
    {
        $this->hydrate($readRawData);
    }

    /**
     * @param array $criteria
     *
     * @return Ship[]
     *
     * @throws NoShipException
     */
    public function searchInList(array $criteria) : array
    {
        $result = [];

        foreach ($this->dictionary as $ship) {
            /** @var \App\NameSwitcher\Model\Ship $ship */
            if ($ship->matchCriteria($criteria)) {
                $result[] = $ship;
            }
        }

        if (count($result) === 0) {
            throw new NoShipException(
                'No ship found matching the required criteria:'
                . DisplayTrait::arrayOutput($criteria, false)
            );
        }

        return $result;
    }

    /**
     * @param array $criteria
     *
     * @return \App\NameSwitcher\Model\Ship
     *
     * @throws NoShipException
     * @throws \Exception
     */
    public function randomWithCriteria(array $criteria) : Ship
    {
        $result      = $this->searchInList($criteria);
        $resultCount = count($result);
        $randInt     = random_int(0, $resultCount - 1);

        return $result[$randInt];
    }

    /**
     * @param array $criteria
     * @param bool  $random
     *
     * @return \App\NameSwitcher\Model\Ship
     *
     * @throws MoreThanOneShipException
     * @throws NoShipException
     * @throws \Exception
     */
    public function findOneShip(array $criteria, bool $random = false) : Ship
    {
        if ($random) {
            $ship = $this->randomWithCriteria($criteria);
        } else {
            $result = $this->searchInList($criteria);
            if (count($result) > 1) {
                throw new MoreThanOneShipException(
                    'More than one result found for the given criteria: '
                    . DisplayTrait::arrayOutput($criteria, false)
                );
            }
            $ship = $result[0];
        }

        return $ship;
    }

    /**
     * @param array $data
     */
    protected function hydrate(array $data) : void
    {
        foreach ($data as $element) {
            $ship = new Ship();
            $dataToInject = [];
            foreach (self::FIELDS_NAME as $key => $value) {
                $dataToInject[$value] = $element[$key];
            }
            $ship->hydrate($dataToInject);
            $this->dictionary[] = $ship;
        }
    }
}
