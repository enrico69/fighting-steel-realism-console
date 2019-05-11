<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/05/2019 (dd-mm-YYYY)
 */
namespace App\Controller\DCM;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Index extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home() : Response
    {
        return $this->render(
            'home/home.html.twig',
            []
        )
            ->setSharedMaxAge(0)
            ->setMaxAge(0);
    }
}
