<?php

namespace Acts\CamdramBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Acts\CamdramBundle\Entity\Performance;

/**
 */
class PerformanceController extends FOSRestController
{
    /**
     * weeksPerformancesAction
     *
     * Generates the table data for displaying the show perofrmances in
     * the week beggining with $startOfWeek
     *
     * @param DateTime $startOfWeek The start date of the week.
     */
    public function weeksPerformancesAction($startOfWeek)
    {
        $startDate = $startOfWeek->getTimestamp();
        $endDate = clone $startOfWeek;
        $endDate = $endDate->modify('+6 days')->getTimestamp();

        $repo = $this->getDoctrine()->getEntityManager()->getRepository('ActsCamdramBundle:Performance');

        $performances = $repo->findAuthorizedJoinedToShow($startDate, $endDate);

        $view = $this->view(array('startDate' => $startDate, 'endDate' => $endDate, 'performances' => $performances), 200)
            ->setTemplate('performance/index.html.twig')
            ->setTemplateVar('performances')
        ;

        return $view;
    }
}
