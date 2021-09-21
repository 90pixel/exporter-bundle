<?php

namespace DIA\ExporterBundle\Controller;

use DIA\ExporterBundle\Driver\PdfDriver;
use DIA\ExporterBundle\Driver\XlsxDriver;
use DIA\ExporterBundle\Manager\ExporterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExporterController
 * @package DIA\Controller\ExporterController
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
        // TODO: Custom driver support will be added soon
        $driverClass = XlsxDriver::class;
        if ($exporterManager->getConfig()->type === 'pdf') {
            $driverClass = PdfDriver::class;
        }

        return $exporterManager->getDriver($driverClass)->handle($data);
    }
}