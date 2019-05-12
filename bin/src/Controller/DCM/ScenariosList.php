<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/05/2019 (dd-mm-YYYY)
 */
namespace App\Controller\DCM;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\DynamicCampaignManager\Model\Scenario\ScenarioRepository;
use App\DynamicCampaignManager\Model\Game\SaveGameManager;
use App\DynamicCampaignManager\Log\Logger;

class ScenariosList extends AbstractController
{
    /**
     * @var \App\DynamicCampaignManager\Model\Scenario\ScenarioRepository
     */
    private $scenarioRepository;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \App\DynamicCampaignManager\Model\Game\SaveGameManager
     */
    private $saveGameManager;

    /**
     * @var \App\DynamicCampaignManager\Log\Logger
     */
    private $logger;

    /**
     * ScenariosList constructor.
     *
     * @param \App\DynamicCampaignManager\Model\Scenario\ScenarioRepository $scenarioRepository
     * @param \Symfony\Component\HttpFoundation\RequestStack                $requestStack
     * @param \App\DynamicCampaignManager\Model\Game\SaveGameManager        $saveGameManager
     * @param \App\DynamicCampaignManager\Log\Logger                        $logger
     */
    public function __construct(
        ScenarioRepository $scenarioRepository,
        RequestStack $requestStack,
        SaveGameManager $saveGameManager,
        Logger $logger
    ) {
        $this->scenarioRepository = $scenarioRepository;
        $this->requestStack = $requestStack;
        $this->saveGameManager = $saveGameManager;
        $this->logger = $logger;
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
            ['scenarios' => $this->scenarioRepository->getScenarioList()]
        )
            ->setSharedMaxAge(0)
            ->setMaxAge(0);
    }

    /**
     * @Route("/save/create", methods={"POST"})
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function scenarioStart() : JsonResponse
    {
        $scenarioKey = $this->requestStack->getCurrentRequest()->get('scenario');
        $response = ['success' => true];

        try {
            if (!\is_string($scenarioKey)) {
                throw new \LogicException('Missing scenario key');
            }
            $this->saveGameManager->deleteSaveGame($scenarioKey);
            $this->saveGameManager->createSaveGame($scenarioKey);
        } catch (\Exception $ex) {
            $response = ['success' => false];
            $this->logger->operation($ex->getMessage(), 'error');
        }

        return new JsonResponse($response);
    }
}
