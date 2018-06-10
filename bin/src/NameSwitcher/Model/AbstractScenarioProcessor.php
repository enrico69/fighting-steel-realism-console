<?php
/**
 * Created by Eric COURTIAL.
 * @author <e.courtial30@gmail.com>
 * Date: 18-06-09
 */
namespace App\NameSwitcher\Model;

/**
 * Class AbstractScenarioProcessor
 * @package App\NameSwitcher\Model
 */
abstract class AbstractScenarioProcessor
{
    /**
     * AbstractProcessor constructor.
     *
     * @param array $param
     */
    public function __construct(array $param)
    {
        // To be overrided if necessary
    }

    /**
     * @return void
     */
    abstract public function processScenario() : void;
}
