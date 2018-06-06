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
        $this->output->write(sprintf("\033\143"));
    }

    /**
     * @param string $title
     */
    protected function outputTitle(string $title) : void
    {
        $this->output->writeln('---------------------------------');
        $this->output->writeln(' ' . $title);
        $this->output->writeln('---------------------------------');
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
}
