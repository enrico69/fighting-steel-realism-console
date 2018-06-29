<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-05
 */
namespace App\NameSwitcher\Model\Dictionary;

use App\NameSwitcher\Model\Ship;
use App\NameSwitcher\Model\Dictionary\Report;
use App\NameSwitcher\Model\Dictionary\Reader as DictionaryReader;

/**
 * Class Validator
 * @package App\NameSwitcher\Model\Dictionary
 */
class Validator
{
    /**
     * @param array $rawData
     * @param bool  $issueReport
     *
     * @return array
     */
    public static function validate(array $rawData, bool $issueReport = false) : array
    {
        $report       = [];
        $shipNameList = [];

        if (count($rawData) === 0) {
            $report[] = Report::getReportElement(
                Report::STRING_GRAVITY_NOTICE,
                0,
                'Dictionary is empty'
            );
        }

        foreach ($rawData as $key => $element) {
            $elementSize = count($element);
            $key++;

            if ($elementSize !== Ship::FIELD_QTY) {
                $report[] = Report::getReportElement(
                    Report::STRING_GRAVITY_ERROR,
                    $key,
                    'Invalid quantity of field'
                );
            }

            $elementSize--;
            foreach ($element as $fieldPos => $fieldValue) {
                if ($fieldPos !== $elementSize) { // 'Similar to' field is optional
                    if (mb_strlen(trim($fieldValue)) === 0) {
                        $report[] = Report::getReportElement(
                            Report::STRING_GRAVITY_ERROR,
                            $key,
                            'A field is empty'
                        );
                        break;
                    }
                    if ($fieldPos === 2) {
                        if (in_array($fieldValue, $shipNameList)) {
                            $report[] = Report::getReportElement(
                                Report::STRING_GRAVITY_ERROR,
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
                $report[] = Report::getReportElement(
                    Report::STRING_GRAVITY_NOTICE,
                    $key,
                    'There is not similar ships given'
                );
            }

            // @Todo : add the verification that the FS Name really exists in the game
        }

        if ($issueReport && !empty($report)) {
            Report::issueReport($report);
        }

        return $report;
    }

    /**
     * Validate the standard dictionary (at the root of the application).
     * Should be removed when the feature allowing to selected a dictionary
     * will be implemented.
     *
     * @param bool $generateReport
     *
     * @return array
     */
    public static function validateStandardDictionary(bool $generateReport) : array
    {
        return static::validate(
            DictionaryReader::readFile(
                DictionaryReader::getDictionaryPath(),
                ';',
                1000,
                false
            ),
            $generateReport
        );
    }
}
