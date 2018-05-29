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
     * Configure the command
     */
    protected function configure() : void
    {
        parent::configure();
        $this->setName('app:launch-fsrc')
            ->setDescription('Launch the Fighting Steel Realism Console');
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
        $this->displayMenu();
    }

    /**
     * Display the main menu
     */
    protected function displayMenu() : void
    {
        $menuContent = [
            1 => 'Launch FS Switcher',
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
}
