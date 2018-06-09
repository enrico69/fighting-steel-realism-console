<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-03
 */
namespace App\NameSwitcher\Model;

use App\NameSwitcher\Model\AbstractScenarioProcessor;
use App\Core\Model\Directory;
use App\NameSwitcher\Model\TasToFs;

/**
 * Class FsToTas
 * @package App\NameSwitcher\Model
 */
class FsToTas extends AbstractScenarioProcessor
{
    public const SCENARIO_BATTLE_REPORT_SAVE = '_End Of Engagement.sce.bak';

    /**
     * @return void
     *
     * @throws \LogicException
     */
    public function processScenario() : void
    {
        $this->saveBattleReportCopy();

        $revertContent = $this->getAfterScenarioFileContent(
            static::getBattleReportCopyFullpath(),
            'battle report copy'
        );
        $revertDictionaryContent = $this->getAfterScenarioFileContent(
            TasToFs::getScenarioRevertDictionaryFullPath(),
            'scenario revert dictionary'
        );

        $rowNumber = 0;
        foreach ($revertContent as &$line) {
            if (strpos($line, 'NAME =') === 0) {
                $newName  = $this->getRevertedShipName($rowNumber, $revertDictionaryContent);
                $line     = 'NAME = ' . $newName;
                $rowNumber++;
            }
            $line .= PHP_EOL;
        }
        unset($line);

        $this->outputRestoredBattleReport($revertContent);
    }

    /**
     * @param int   $rowNumber
     * @param array $content
     *
     * @return string
     *
     * @throws \LogicException
     */
    protected function getRevertedShipName(int $rowNumber, array &$content) : string
    {
        if (empty($content[$rowNumber])) {
            throw new \LogicException(
                'The scenario revert dictionnay seems to be corrupted: '
                . "the line numer $rowNumber seems to be missing"
            );
        }

        $data = explode('|', $content[$rowNumber]);
        if (empty($data[1])) {
            throw new \LogicException(
                'The scenario revert dictionnay seems to be corrupted: '
                . "the line numer $rowNumber seems to be at the wrong format"
            );
        }

        return trim($data[1]);
    }

    /**
     * @return string
     */
    protected static function getBattleReportCopyFullpath() : string
    {
        return Directory::getScenarioDirectory() . self::SCENARIO_BATTLE_REPORT_SAVE;
    }

    /**
     * @throws \LogicException
     */
    protected function saveBattleReportCopy() : void
    {
        $status = rename(
            TasToFs::getEndOfEngagementFullPath(),
            static::getBattleReportCopyFullpath()
        );

        if (!$status) {
            throw new \LogicException('Impossible to make a copy of the battle report.');
        }
    }

    /**
     * @param array $content
     *
     * @return void
     *
     * @throws \LogicException
     */
    protected function outputRestoredBattleReport(array &$content) : void
    {
        $result = file_put_contents(
            TasToFs::getEndOfEngagementFullPath(),
            $content
        );

        if ($result === false) {
            throw new \LogicException('Impossible to output the new battle report file.');
        }
    }

    /**
     * @param string $filename
     * @param string $label
     *
     * @return array
     *
     * @throws \LogicException
     */
    protected function getAfterScenarioFileContent(string $filename, string $label) : array
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
