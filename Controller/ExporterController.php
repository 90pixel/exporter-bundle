<?php

namespace DIA\ExporterBundle\Controller;

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
        return $exporterManager->getDriver(XlsxDriver::class)->handle($data);
    }
}