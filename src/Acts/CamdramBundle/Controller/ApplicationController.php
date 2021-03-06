<?php

namespace Acts\CamdramBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpFoundation\Request;

use Acts\CamdramBundle\Service\Time;
use Acts\CamdramBundle\Entity\Application;
use Doctrine\Common\Collections\Criteria;

/**
 * @RouteResource("Application")
 */
class ApplicationController extends FOSRestController
{
    /**
     * cgetAction
     *
     * Display application deadlines from now until the end of (camdram) time
     */
    public function cgetAction(Request $request)
    {
        $applications = array_reverse($this->getDoctrine()->getRepository('ActsCamdramBundle:Application')
            ->findLatest(-1, Time::now()));
        
        $view = $this->view($applications, 200)
                  ->setTemplate('application/index.'.$request->getRequestFormat().'.twig')
                   ->setTemplateVar('applications')
               ;
        return $view;
    }

    public function getAction($identifier, Request $request)
    {
        $data = $this->getDoctrine()->getRepository('ActsCamdramBundle:Application')
            ->findOneBySlug($identifier, Time::now());
            
        if (!$data) {
            throw $this->createNotFoundException('No application exists with that identifier');
        }

        if ($request->getRequestFormat() == 'html') {
            return $this->redirect($this->generateUrl('get_applications').'#'.$identifier);
        } else {
            return $this->view($data);
        }
    }

    /**
     * weeksApplicationsAction
     *
     * Generates the table data for displaying application deadlines in
     * the week beggining with $startOfWeek
     *
     * @param DateTime $startOfWeek The start date of the week.
     */
    public function weeksApplicationsAction($startOfWeek)
    {
        $startDate = $startOfWeek->getTimestamp();
        $endDate = clone $startOfWeek;
        $endDate = $endDate->modify('+6 days')->getTimestamp();

        $repo = $this->getDoctrine()->getEntityManager()->getRepository('ActsCamdramBundle:Application');

        $applications = $repo->findScheduledOrderedByDeadline($startDate, $endDate);

        $view = $this->view(array('startDate' => $startDate, 'endDate' => $endDate, 'applications' => $applications), 200)
            ->setTemplate('application/diary.html.twig')
            ->setTemplateVar('applications')
        ;

        return $view;
    }
}
