<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-29
 */
namespace App\NameSwitcher\Console;

use App\NameSwitcher\Model\Dictionary\ScenarioValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use App\Core\Tools\DisplayTrait;
use App\Core\Tools\InputTrait;
use App\NameSwitcher\Model\TasToFs;
use App\Core\Model\Configuration;
use App\NameSwitcher\Model\Dictionary\Validator as DictionaryValidator;

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
            4   => 'Check dictionary content against a given TAS scenario',
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
                    $this->tasToFsSideMenu();
                    break;
                case 2:
                    $this->processScenario('FsToTas');
                    break;
                case 3:
                    $exitApplication = $this->checkDictionary();
                    break;
                case 4:
                    $exitApplication = $this->checkScenarioDictionary();
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
     * Display the TAS to FS menu
     */
    protected function displayTasMenu() : void
    {
        $menuContent = [
            1   => 'None: just switching',
            2   => 'Switching with obfuscation',
            3   => 'Switching with obfuscation and confusion',
            4   => 'Let the game decide to make it more realistic',
            'R' => 'Return',
        ];

        $this->outputTitle('From TAS to FS');
        $this->output->writeln('Which level of realism?');
        $this->outputMenu($menuContent);
    }

    /**
     * Display the TAS to FS menu
     */
    protected function displayChooseSideMenu() : void
    {
        $menuContent = [
            1   => 'Allied',
            2   => 'Axis',
            'R' => 'Return',
        ];

        $this->outputTitle('From TAS to FS');
        $this->output->writeln('What is your side?');
        $this->outputMenu($menuContent);
    }

    /**
     * @return bool
     */
    protected function tasToFsSideMenu() : bool
    {
        $exitApplication = false;
        $helper          = $this->getHelper('question');
        $question        = new Question('Which is your side: ', 'q');

        $levelChoices = [
            1 => 'Blue',
            2 => 'Red',
        ];

        while (!$exitApplication) {
            $this->clearScreen();
            $this->displayChooseSideMenu();
            $menuChoice = mb_strtolower(
                $helper->ask($this->input, $this->output, $question)
            );

            if (array_key_exists($menuChoice, $levelChoices)) {
                $this->tasToFsMenu($levelChoices[$menuChoice]);
            } elseif ($menuChoice === 'r') {
                $this->output->writeln('');
                $exitApplication = true;
            }
        }

        return false;
    }

    /**
     * @param string $side
     *
     * @return bool
     */
    protected function tasToFsMenu(string $side) : bool
    {
        $exitApplication = false;
        $helper          = $this->getHelper('question');
        $question        = new Question('Which level of obfuscation: ', 'q');

        $levelChoices = [
            1 => TasToFs::SWITCH_LEVEL_BASIC,
            2 => TasToFs::SWITCH_LEVEL_OBFUSCATE,
            3 => TasToFs::SWITCH_LEVEL_OBFUSCATE_CONFUSED,
            4 => TasToFs::SWITCH_AUTO_SELECTION,
        ];

        while (!$exitApplication) {
            $this->clearScreen();
            $this->displayTasMenu();
            $menuChoice = mb_strtolower(
                $helper->ask($this->input, $this->output, $question)
            );

            if (array_key_exists($menuChoice, $levelChoices)) {
                $this->processScenario(
                    'TasToFs',
                    ['level' => $levelChoices[$menuChoice], 'side' => $side]
                );
            } elseif ($menuChoice === 'r') {
                $this->output->writeln('');
                $exitApplication = true;
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

    /**
     * @param string $process
     * @param array  $param
     */
    protected function processScenario(string $process, array $param = []) : void
    {
        try {
            $process = 'App\NameSwitcher\Model\\' . $process;
            /** @var \App\NameSwitcher\Model\AbstractScenarioProcessor $module */
            $module = new $process($param);
            $module->processScenario();
            $this->clearScreen();
            $this->output->writeln('Done. Press a key to exit');
            $this->waitForInput(false);
            exit;
        } catch (\Exception $ex) {
            $this->clearScreen();
            $this->output->writeln('An error occurred. The exact message was:');
            $this->output->writeln($ex->getMessage());
            $this->output->writeln('');
            $this->waitForInput();
        }
    }

    /**
     * Allows to validate the dictionary
     *
     * @return bool
     */
    protected function checkDictionary() : bool
    {
        $this->clearScreen();

        if (empty(DictionaryValidator::validateStandardDictionary(true))) {
            $this->output->writeln('Everything seems to be fine!');
        } else {
            $msg = 'Something was found wrong in the dictionary.'
                . 'The report has been generated.';
            $this->output->writeln($msg);
        }

        $this->waitForInput();

        return false;
    }

    /**
     * Allows to validate the dictionary AND the scenario files
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function checkScenarioDictionary() : bool
    {
        $helper         = $this->getHelper('question');
        $question       = new Question("Enter the scenario folder name, e.g : 'Graff Spee': ");
        $scenarioFolder = $helper->ask($this->input, $this->output, $question);
        $question       = new Question("Enter the scenario ship file name, e.g : 'GS.scn': ");
        $fsShipFile     = $helper->ask($this->input, $this->output, $question);

        $this->clearScreen();
        $this->output->writeln("Checking scenario '$scenarioFolder'");

        if (empty(ScenarioValidator::validate($scenarioFolder, $fsShipFile, true))) {
            $this->output->writeln('Everything seems to be fine!');
        } else {
            $msg = 'Something was found wrong in the scenario validation.'
                . 'The report has been generated.';
            $this->output->writeln($msg);
        }

        $this->waitForInput();

        return false;
    }
}
