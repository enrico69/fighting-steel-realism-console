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
use App\Core\Display\DisplayTrait;

class TasMenu extends Command
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
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return bool
     */
    public function execute(InputInterface $input, OutputInterface $output) : bool
    {
        $this->output = $output;
        $this->input  = $input;

        return $this->handleInput();
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

        $this->outputTitle('Fighting Steel Name Switcher');
        $this->output->writeln('In which direction are you switching?');
        $this->outputMenu($menuContent);
    }

    /**
     * Handle the user choice
     *
     * @return bool
     */
    protected function handleInput() : bool
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
                    $this->output->writeln('AH');
                    $exitApplication = true;
                    break;
                case 2:
                    $this->output->writeln('OH');
                    $exitApplication = true;
                    break;
                case 'r':
                    $this->output->writeln('');
                    $exitApplication = true;
                    break;
            }
        }

        return false;
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
