<?php

namespace DPX\ExporterBundle\Manager;

use DPX\ExporterBundle\Annotation\ExporterConfig;
use DPX\ExporterBundle\Helper\ExporterHelper;
use DPX\ExporterBundle\Interfaces\DriverInterface;
use DPX\ExporterBundle\Interfaces\ExporterInterface;
use DPX\ExporterBundle\Reader\ConfigReader;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExporterManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Request
     */
    private $request;

    public function __construct(ContainerInterface $container, RequestStack $request)
    {
        $this->container = $container;
        $this->request = $request->getCurrentRequest();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResourceClass(): string
    {
        return $this->getRequest()->attributes->get('_api_resource_class');
    }

    public function getOperationName(): string
    {
        return $this->getRequest()->attributes->get('_api_collection_operation_name');
    }

    public function getConfig(): ?ExporterConfig
    {
        return ConfigReader::read($this->getResourceClass(), $this->getOperationName());
    }

    public function getExporter(): ExporterInterface
    {
        $exporterClass = $this->container->get(ExporterHelper::class);
        if ($this->getConfig()->exporterClass) {
            $exporterClass = $this->container->get($this->getConfig()->exporterClass);
        }

        $exporterClass->setConfig($this->getConfig());

        return $exporterClass;
    }

    public function getDriver(string $driver): DriverInterface
    {
        return $this->container->get($driver);
    }
}