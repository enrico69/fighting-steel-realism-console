<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/05/2019 (dd-mm-YYYY)
 */
namespace App\Controller\DCM;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\DynamicCampaignManager\Model\Scenario\ScenarioReader;

class ScenariosList extends AbstractController
{
    /**
     * @var \App\DynamicCampaignManager\Model\Scenario\ScenarioReader
     */
    private $scenarioReader;

    /**
     * ScenariosList constructor.
     * @param \App\DynamicCampaignManager\Model\Scenario\ScenarioReader $scenarioReader
     */
    public function __construct(ScenarioReader $scenarioReader)
    {
        $this->scenarioReader = $scenarioReader;
    }

    /**
     * @Route("/scenarios", name="scenarios")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home() : Response
    {
        return $this->render(
            'home/scenarios.html.twig',
            ['scenarios' => $this->scenarioReader->getScenarioList()]
        )
            ->setSharedMaxAge(0)
            ->setMaxAge(0);
    }
}
