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
     * @return void
     */
    protected function waitForInput() : void
    {
        $question = new Question('Press a key to return to menu.');
        $this->getHelper('question')->ask($this->input, $this->output, $question);
    }
}
