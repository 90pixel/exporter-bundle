<?php

namespace DPX\ExporterBundle\Controller;

use DPX\ExporterBundle\Driver\PdfDriver;
use DPX\ExporterBundle\Driver\XlsxDriver;
use DPX\ExporterBundle\Manager\ExporterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExporterController
 * @package DPX\Controller\ExporterController
 */
class ExporterController extends AbstractController
{
    /**
     * @param $data
     * @param ExporterManager $exporterManager
     * @return Response
     */
    public function __invoke($data, ExporterManager $exporterManager): Response
    {
        return $exporterManager->getDriver()->handle($data);
    }
}