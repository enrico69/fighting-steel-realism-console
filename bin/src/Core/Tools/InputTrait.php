<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-05
 */
namespace App\Core\Tools;

use Symfony\Component\Console\Question\Question;

/**
 * Trait InputTrait
 * @package App\Core\Display
 */
trait InputTrait
{
    /**
     * @param bool $displayMessage
     *
     * @return void
     */
    protected function waitForInput($displayMessage = true) : void
    {
        $questionLabel = '';
        if ($displayMessage) {
            $questionLabel = 'Press a key to return to menu.';
        }
        $question = new Question($questionLabel);
        $this->getHelper('question')->ask($this->input, $this->output, $question);
    }
}
