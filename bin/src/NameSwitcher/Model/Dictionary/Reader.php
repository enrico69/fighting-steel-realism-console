<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-05
 */
namespace App\NameSwitcher\Model\Dictionary;

use App\NameSwitcher\Model\Ship;
use App\Core\Model\Directory;

/**
 * Class Reader
 * @package App\NameSwitcher\Model\Dictionary
 */
class Reader
{
    /**
     * @param string $fullPath
     * @param string $separator
     * @param int    $lineLength
     * @param bool   $validationError
     *
     * @return Ship[]
     */
    public static function readFile(
        string $fullPath,
        string $separator = ';',
        int $lineLength = 1000,
        bool $validationError = true
    ) : array {
        $dataRead = [];
        $row      = 1;
        if (($handle = fopen($fullPath, 'r')) !== false) {
            while (($data = fgetcsv($handle, $lineLength, $separator)) !== false) {
                if ($row > 1) { // Ignore header
                    if ($validationError && count($data) !== Ship::FIELD_QTY) {
                        throw new \LogicException('Invalid quantity of field in the dictionary at line' . $row);
                    }
                    $dataRead[] = $data;
                }
                $row++;
            }
            fclose($handle);
        } else {
            throw new \LogicException('Impossible to open the dictionary file');
        }

        return $dataRead;
    }

    /**
     * @return void
     */
    public static function checkFilePresence() : void
    {
        $fullPath = self::getDictionaryPath();
        if (!file_exists($fullPath)) {
            throw new \LogicException('Sorry, the dictionary file has not been found');
        }
    }

    /**
     * @TODO to be refactored when it will be possible to select a dictionary
     *
     * @return string
     */
    public static function getDictionaryPath() : string
    {
        return Directory::getRootPath() . 'dictionary.csv';
    }
}
