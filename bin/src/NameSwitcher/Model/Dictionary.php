<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-26
 */
namespace App\NameSwitcher\Model;

use App\NameSwitcher\Model\Ship;

class Dictionary
{
    /**
     * @var Ship[]
     */
    protected $dictionary = [];

    /**
     * @param array $criteria
     *
     * @return Ship[]
     *
     * @throws \LogicException
     */
    public function searchInList(array $criteria) : array
    {
        $result = [];

        foreach ($this->dictionary as $ship) {
            /** @var \NameSwitcher\Model\Ship $ship */
            if ($ship->matchCriteria($criteria)) {
                $result[] = $ship;
            }
        }

        if (count($result) === 0) {
            throw new \LogicException('No ship found matching the required criteria');
        }

        return $result;
    }

    /**
     * @param array $criteria
     *
     * @return \NameSwitcher\Model\Ship
     *
     * @throws \LogicException
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
     * Dictionary constructor.
     *
     * @param string $fullPath
     * @param string $separator
     * @param int    $lineLength
     */
    public function __construct(
        string $fullPath,
        string $separator = ';',
        int $lineLength   = 1000
    ) {
        $this->dictionary = $this->readFile($fullPath, $separator, $lineLength);
    }

    /**
     * @param array $criteria
     * @param bool  $random
     *
     * @return \NameSwitcher\Model\Ship
     *
     * @throws \LogicException
     * @throws \Exception
     */
    public function findOneShip(array $criteria, bool $random = false) : Ship
    {
        if ($random) {
            $ship = $this->randomWithCriteria($criteria);
        } else {
            $result = $this->searchInList($criteria);
            if (count($result) > 1) {
                throw new \LogicException('More than one result found for the given criteria');
            }
            $ship = $result[0];
        }

        return $ship;
    }

    /**
     * @param string $fullPath
     * @param string $separator
     * @param int    $lineLength
     *
     * @return Ship[]
     */
    protected function readFile(
        string $fullPath,
        string $separator,
        int $lineLength
    ) : array {
        $dataRead = [];
        $row      = 1;
        if (($handle = fopen($fullPath, 'r')) !== false) {
            while (($data = fgetcsv($handle, $lineLength, $separator)) !== false) {
                if (count($data) !== Ship::FIELD_QTY) {
                    throw new \LogicException('Invalid quantity of field in the dictionary at line' . $row);
                }
                $dataRead[] = $data;
                $row++;
            }
            fclose($handle);
        }

        return $dataRead;
    }
}
