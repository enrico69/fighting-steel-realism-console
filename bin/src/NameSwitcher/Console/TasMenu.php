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
use App\Core\Tools\DisplayTrait;
use App\Core\Tools\InputTrait;
use App\NameSwitcher\Model\TasToFs;
use App\Core\Model\Configuration;
use App\NameSwitcher\Model\Dictionary\Validator as DictionaryValidator;
use App\NameSwitcher\Model\Dictionary\Reader as DictionaryReader;

/**
 * Class TasMenu
 * @package App\NameSwitcher\Console
 */
class TasMenu extends Command
{
    use DisplayTrait;
    use InputTrait;

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
        Configuration::getConfigurationFileContent();
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
            3   => 'Check dictionary content',
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
                    $exitApplication = $this->tasToFsMenu();
                    break;
                case 2:
                    $exitApplication = $this->FsToTas();
                    break;
                case 3:
                    $exitApplication = $this->checkDictionary();
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
     * @return bool
     */
    protected function tasToFsMenu() : bool
    {
        $exitApplication = false;
        $helper          = $this->getHelper('question');
        $question        = new Question('Which level of obfuscation: ', 'q');

        $levelChoices = [
            1 => TasToFs::SWITCH_LEVEL_BASIC,
            2 => TasToFs::SWITCH_LEVEL_OBFUSCATE,
            3 => TasToFs::SWITCH_LEVEL_OBFUSCATE_CONFUSED,
        ];

        while (!$exitApplication) {
            $this->clearScreen();
            $this->displayTasMenu();
            $menuChoice = mb_strtolower(
                $helper->ask($this->input, $this->output, $question)
            );

            if (array_key_exists($menuChoice, $levelChoices)) {
                $this->launchTasToFsProcess($levelChoices[$menuChoice]);
            } elseif ($menuChoice === 'r') {
                $this->output->writeln('');
                $exitApplication = true;
            }
        }

        return false;
    }

    /**
     * Display the TAS to FS menu
     */
    protected function displayTasMenu() : void
    {
        $menuContent = [
            1   => 'None: just switching',
            2   => 'Switching with obfuscation',
            3   => 'Switching with obfuscation and confusion',
            'R' => 'Return',
        ];

        $this->outputTitle('From TAS to FS');
        $this->output->writeln('Which level of realism?');
        $this->outputMenu($menuContent);
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

    /**
     * @param string $obfuscatingLevel
     */
    protected function launchTasToFsProcess($obfuscatingLevel) : void
    {
        try {
            $module = new TasToFs($obfuscatingLevel);
            $module->processScenario();
        } catch (\Exception $ex) {
            $this->clearScreen();
            $this->output->writeln('An error occured. The exact message was:');
            $this->output->writeln($ex->getMessage());
        }
    }

    /**
     * Process from FS to TAS
     */
    protected function FsToTas() : void
    {

    }

    /**
     * @return bool
     */
    protected function checkDictionary() : bool
    {
        DictionaryValidator::validate(
            DictionaryReader::readFile(
                DictionaryReader::getDictionaryPath(),
                ';',
                1000,
                false
            ),
            true
        );

        $this->clearScreen();
        $this->output->writeln('The report has been generated');
        $this->waitForInput();

        return false;
    }
}
