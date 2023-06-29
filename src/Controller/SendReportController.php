<?php

namespace App\Controller;

use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SendReportController extends AbstractController
{
    /**
     * @Route("/", name="app_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('send_report/index.html.twig');
    }

    /**
     * @Route("/", name="app_submit", methods={"POST"})
     */
    public function submit(Request $request, ReportService $reportService): Response
    {
        $date = new \DateTimeImmutable($request->get('date'));
        $reportService->execute($date);

        return $this->render('send_report/submit.html.twig');
    }
}
