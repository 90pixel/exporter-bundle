<?php

namespace DPX\ExporterBundle\Driver;

use DPX\ExporterBundle\Helper\DriverHelper;
use DPX\ExporterBundle\Manager\ExporterManager;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class PdfDriver extends DriverHelper
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(ExporterManager $exporterManager, Environment $twig)
    {
        parent::__construct($exporterManager);
        $this->twig = $twig;
    }

    public function handle($data): Response
    {
        $manager = $this->getExporterManager();
        $exporter = $manager->getExporter();
        $config = $manager->getConfig();

        $htmlOutput = $this->twig->render($config->templateName, [
            'headers' => $exporter->headers,
            'results' => $data
        ]);

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($htmlOutput);

        $response =  new StreamedResponse(function () use ($mpdf) {
            $mpdf->Output();
        });
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s"', $this->getFileName()));
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->prepare($this->getExporterManager()->getRequest());
        $response->sendHeaders();

        return $response;
    }
}