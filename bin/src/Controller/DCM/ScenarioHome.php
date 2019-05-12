<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/05/2019 (dd-mm-YYYY)
 */
namespace App\Controller\DCM;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\DynamicCampaignManager\Model\Scenario\ScenarioRepository;

class ScenarioHome extends AbstractController
{
    /**
     * @var \App\DynamicCampaignManager\Model\Scenario\ScenarioRepository
     */
    private $scenarioRepository;

    /**
     * ScenariosList constructor.
     *
     * @param \App\DynamicCampaignManager\Model\Scenario\ScenarioRepository $scenarioRepository
     */
    public function __construct(ScenarioRepository $scenarioRepository)
    {
        $this->scenarioRepository = $scenarioRepository;
    }

    /**
     * @Route("/scenario/{scenarioId}/{color}", name="scenario")
     *
     * @param string $scenarioId
     * @param string $color
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function controlRoom(string $scenarioId, string $color) : Response
    {
        $scenario = $this->scenarioRepository->getByKey($scenarioId);

        return $this->render(
            'scenario/control_room.html.twig',
            ['scenario' => $scenario]
        )
            ->setSharedMaxAge(0)
            ->setMaxAge(0);
    }
}
