<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-05
 */
namespace App\NameSwitcher\Model\Dictionary;

use App\Core\Model\Configuration;
use App\NameSwitcher\Model\Ship;

/**
 * Class Validator
 * @package App\NameSwitcher\Model\Dictionary
 */
class Validator
{
    const STRING_GRAVITY_NOTICE = 'Notice';
    const STRING_GRAVITY_ERROR  = 'Error';

    /**
     * @param array $rawData
     * @param bool  $issueReport
     *
     * @return array
     */
    public static function validate(array $rawData, $issueReport = false) : array
    {
        $report       = [];
        $shipNameList = [];

        if (count($rawData) === 0) {
            $report[] = static::getReportElement(
                self::STRING_GRAVITY_NOTICE,
                0,
                'Dictionary is empty'
            );
        }

        foreach ($rawData as $key => $element) {
            $elementSize = count($element);
            $key++;

            if ($elementSize !== Ship::FIELD_QTY) {
                $report[] = static::getReportElement(
                    self::STRING_GRAVITY_ERROR,
                    $key,
                    'Invalid quantity of field'
                );
            }

            $elementSize--;
            foreach ($element as $fieldPos => $fieldValue) {
                if ($fieldPos !== $elementSize) { // 'Similar to' field is optional
                    if (mb_strlen(trim($fieldValue)) === 0) {
                        $report[] = static::getReportElement(
                            self::STRING_GRAVITY_ERROR,
                            $key,
                            'A field is empty'
                        );
                        break;
                    }
                    if ($fieldPos === 1) {
                        if (in_array($fieldValue, $shipNameList)) {
                            $report[] = static::getReportElement(
                                self::STRING_GRAVITY_ERROR,
                                $key,
                                "There is more than one entry with the ship name '$fieldValue'"
                            );
                        } else {
                            $shipNameList[] = $fieldValue;
                        }
                    }
                }
            }

            if (mb_strlen(trim($element[$elementSize])) === 0) {
                $report[] = static::getReportElement(
                    self::STRING_GRAVITY_NOTICE,
                    $key,
                    'There is not similar ships given'
                );
            }

            // @Todo : add the verification that the FS Name really exists in the game
        }

        if ($issueReport) {
            static::issueReport($report);
        }

        return $report;
    }

    /**
     * @param string $gravity
     * @param int    $line
     * @param string $message
     *
     * @return string
     */
    protected static function getReportElement($gravity, $line, $message) : string
    {
        return "$gravity at line {$line}: $message";
    }

    /**
     * @param array $errorList
     *
     * @throws \LogicException
     */
    protected static function issueReport(array $errorList) : void
    {
        foreach ($errorList as &$line) {
            $line .= PHP_EOL;
        }
        unset($line);

        $now = new \DateTime();
        $filename = $now->format('Y-m-d H:i:s') . '-dictionary-report.txt';
        $filename = Configuration::getRootPath() . $filename;
        $result = file_put_contents($filename, $errorList);

        if ($result === false) {
            throw new \LogicException('Impossible to output the dictionary report');
        }
    }
}
