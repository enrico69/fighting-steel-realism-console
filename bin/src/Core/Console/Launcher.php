<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-28
 */
namespace App\Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class Launcher
 * @package Core\Console
 */
class Launcher extends Command
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
     * Configure the command
     */
    protected function configure() : void
    {
        parent::configure();
        $this->setName('app:launch-fsrc')
            ->setDescription('Directly launch the Fighting Steel Realism Console');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->output = $output;
        $this->input  = $input;
        $this->displayMenu();
        $this->handleInput();
    }

    /**
     * Display the main menu
     */
    protected function displayMenu() : void
    {
        $menuContent = [
            1 => 'Launch FS Name Switcher',
            'Q' => 'Exit the application',
        ];

        $this->output->writeln('What do you want to do?');
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
                    $this->launchSubModule('TasMenu', $choiceIsCorrect);
                    break;
                case 'q':
                    $this->output->writeln('Bye!');
                    $choiceIsCorrect = true;
                    break;
                default:
                    $this->output->writeln('Unknown choice');
                    $menuChoice = $helper->ask($this->input, $this->output, $question);
            }
        }
    }

    /**
     * @param string $moduleName
     * @param bool   $choiceIsCorrect
     *
     * @throws \InvalidArgumentException
     */
    protected function launchSubModule(string $moduleName, bool  &$choiceIsCorrect) : void
    {
        $choiceIsCorrect = true;
        $this->output->writeln('');

        switch ($moduleName) {
            case 'TasMenu':
                $moduleName = 'App\NameSwitcher\Console\TasMenu';
                break;
            default:
                throw new \InvalidArgumentException('Unknown submodule');
        }

        /** @var \Symfony\Component\Console\Command\Command $subModule */
        $subModule = new $moduleName();
        $subModule->setHelperSet($this->getHelperSet());
        $subModule->execute($this->input, $this->output);
    }
}
