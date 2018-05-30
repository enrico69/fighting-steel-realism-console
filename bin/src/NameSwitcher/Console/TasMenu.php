<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-29
 */
namespace App\NameSwitcher\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TasMenu extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->output = $output;
        $this->input  = $input;
        $this->displayMenu();
        $this->handleInput();
    }

    /**
     * Display the menu
     */
    protected function displayMenu() : void
    {
        $menuContent = [
            1   => 'TAS to FS',
            2   => 'FS to TAS',
            'R' => 'Return to main menu',
        ];

        $this->output->writeln('In which direction are you switching?');
        $elementsCount = count($menuContent);
        $row           = 1;
        foreach ($menuContent as $shortcut => $label) {
            $this->output->writeln("{$shortcut}- $label");
            $row++;

            if ($row === $elementsCount) {
                $this->output->writeln('');
            }
        }
    }

    /**
     * Handle the user choice
     */
    protected function handleInput() : void
    {
        $choiceIsCorrect = false;
        $helper          = $this->getHelper('question');
        $question        = new Question('Enter your choice: ', 'q');
        $menuChoice      = mb_strtolower(
            $helper->ask($this->input, $this->output, $question)
        );

        while (!$choiceIsCorrect) {
            switch ($menuChoice) {
                case 1:
                    $this->output->writeln('AH');
                    $choiceIsCorrect = true;
                    break;
                case 2:
                    $this->output->writeln('OH');
                    $choiceIsCorrect = true;
                    break;
                case 'r':
                    $this->output->writeln('');
                    $choiceIsCorrect = true;
                    break;
                default:
                    $menuChoice = $helper->ask($this->input, $this->output, $question);
            }
        }
    }

    /**
     * Configure the command
     */
    protected function configure() : void
    {
        parent::configure();
        $this->setName('app:launch-fsrc-ns')
            ->setDescription('Fighting Steel Name Switcher');
    }
}
