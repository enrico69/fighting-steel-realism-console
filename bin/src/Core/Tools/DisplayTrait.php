<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-05-29
 */
namespace App\Core\Tools;

/**
 * Trait Display
 * @package App\Core\Display\Tools
 */
trait DisplayTrait
{
    /**
     * Clear the screen
     */
    protected function clearScreen() : void
    {
        // Does not work on windows... Damned!
        $this->output->write(sprintf("\033\143"));
    }

    /**
     * @param string $title
     */
    protected function outputTitle(string $title) : void
    {
        $titleLength = mb_strlen(trim($title));
        $border      = str_repeat('-', $titleLength + 4);

        $this->output->writeln($border);
        $this->output->writeln('  ' . $title);
        $this->output->writeln($border);
        $this->output->writeln('');
    }

    /**
     * @param array $menuContent
     */
    protected function outputMenu(array $menuContent) : void
    {
        $elementsCount = count($menuContent);
        $row           = 1;
        foreach ($menuContent as $shortcut => $label) {
            $this->output->writeln("{$shortcut} - $label");
            $row++;

            if ($row === $elementsCount) {
                $this->output->writeln('');
            }
        }
    }

    /**
     * @param array $data
     * @param bool  $display
     *
     * @return string
     */
    public static function arrayOutput(array $data, $display = true) : string
    {
        $returned = '';
        $output = '<pre>' . print_r($data, true) . '</pre>';
        if ($display) {
            echo $output;
        } else {
            $returned = $output;
        }

        return $returned;
    }
}
