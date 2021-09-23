<?php

namespace DPX\ExporterBundle\Helper;

use DPX\ExporterBundle\Interfaces\DriverInterface;
use DPX\ExporterBundle\Manager\ExporterManager;
use Symfony\Component\HttpFoundation\Response;

class DriverHelper implements DriverInterface
{
    /**
     * @var ExporterManager
     */
    private $exporterManager;

    public function __construct(ExporterManager $exporterManager)
    {
        $this->exporterManager = $exporterManager;
    }

    public function handle($data): Response
    {
        return new Response('Exporter Bundle');
    }

    public function getExporterManager(): ExporterManager
    {
        return $this->exporterManager;
    }

    public function getFileName(): string
    {
        return $this->getExporterManager()->getConfig()->filename;
    }
}