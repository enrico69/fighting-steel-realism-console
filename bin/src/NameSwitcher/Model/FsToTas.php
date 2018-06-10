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
use App\Core\Model\File;

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

        $revertContent = File::readTextFileContent(
            static::getBattleReportCopyFullpath(),
            'battle report copy'
        );
        $revertDictionaryContent = File::readTextFileContent(
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
                'The scenario revert dictionary seems to be corrupted: '
                . "the line number $rowNumber seems to be missing"
            );
        }

        $key  = 0;
        $data = explode('|', $content[$rowNumber]);
        if (empty($data[$key])) {
            throw new \LogicException(
                'The scenario revert dictionary seems to be corrupted: '
                . "the line number $rowNumber seems to be at the wrong format"
            );
        }

        return trim($data[$key]);
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
}
