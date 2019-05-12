<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-27
 */
namespace App\NameSwitcher\Model\Dictionary;

use App\Core\Model\Directory;

/**
 * Class Report
 * @package App\NameSwitcher\Model\Dictionary
 */
class Report
{
    const STRING_GRAVITY_NOTICE = 'Notice';
    const STRING_GRAVITY_ERROR  = 'Error';

    /**
     * @param string $gravity
     * @param int    $line
     * @param string $message
     *
     * @return string
     */
    public static function getReportElement($gravity, $line, $message) : string
    {
        return "$gravity at line {$line}: $message";
    }

    /**
     * @param array $errorList
     *
     * @throws \LogicException
     */
    public static function issueReport(array $errorList) : void
    {
        foreach ($errorList as &$line) {
            $line .= PHP_EOL;
        }
        unset($line);

        $now  = new \DateTime();
        $date = $now->format('Y-m-d H:i:s');
        $date = str_replace([' ', ':'], ['-', '-'], $date);
        $filename = $date . '-dictionary-report.txt';
        $filename = Directory::getRootPath() . $filename;
        $result = file_put_contents($filename, $errorList);

        if ($result === false) {
            throw new \LogicException('Impossible to output the dictionary report');
        }
    }
}
