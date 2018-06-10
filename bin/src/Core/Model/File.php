<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-10
 */
namespace App\Core\Model;

/**
 * Class File
 * @package App\Core\Model
 */
class File
{
    /**
     * @param string $filename
     * @param string $label
     *
     * @return array
     *
     * @throws \LogicException
     */
    public static function readTextFileContent(string $filename, string $label) : array
    {
        $content = [];
        $handle  = @fopen($filename, 'r');

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $content[] = trim($buffer);
            }
            fclose($handle);
        } else {
            throw new \LogicException("Impossible to read the content of the {$label}.");
        }

        return $content;
    }
}
