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
use App\Core\Tools\DisplayTrait;

/**
 * Class Launcher
 * @package Core\Console
 */
class Launcher extends Command
{
    use DisplayTrait;

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
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->output = $output;
        $this->input  = $input;
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

        $this->outputTitle('Fighting Steel Realism Console v1.0');
        $this->output->writeln('What do you want to do?');
        $this->outputMenu($menuContent);
    }

    /**
     * Handle the user choice
     */
    protected function handleInput() : void
    {
        $exitApplication = false;
        $helper          = $this->getHelper('question');
        $question        = new Question('Enter your choice: ', 'q');

        while (!$exitApplication) {
            $this->clearScreen();
            $this->displayMenu();
            $menuChoice = mb_strtolower(
                $helper->ask($this->input, $this->output, $question)
            );

            switch ($menuChoice) {
                case 1:
                    $exitApplication = $this->launchSubModule('TasMenu');
                    break;
                case 'q':
                    $this->output->writeln('Abandon ship!');
                    $exitApplication = true;
                    break;
            }
        } // End display menu
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    protected function launchSubModule(string $moduleName) : bool
    {
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

        return $subModule->execute($this->input, $this->output);
    }
}
