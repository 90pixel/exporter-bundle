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

    /**
     * @param ContainerInterface $container
     * @param RequestStack $request
     */
    public function __construct(ContainerInterface $container, RequestStack $request)
    {
        $this->container = $container;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->getRequest()->attributes->get('_api_resource_class');
    }

    /**
     * @return string
     */
    public function getOperationName(): string
    {
        return $this->getRequest()->attributes->get('_api_collection_operation_name');
    }

    /**
     * @return ExporterConfig|null
     */
    public function getConfig(): ?ExporterConfig
    {
        return ConfigReader::read($this->getResourceClass(), $this->getOperationName());
    }

    /**
     * @return ExporterInterface
     */
    public function getExporter(): ExporterInterface
    {
        $exporterClass = $this->container->get(ExporterHelper::class);
        if ($this->getConfig()->exporterClass) {
            $exporterClass = $this->container->get($this->getConfig()->exporterClass);
        }

        $exporterClass->setConfig($this->getConfig());

        return $exporterClass;
    }

    /**
     * @param string|null $driver
     * @return DriverInterface
     */
    public function getDriver(string $driver = null): DriverInterface
    {
        if (!$driver) {
            $driver = $this->getConfig()->driver;
        }

        return $this->container->get($driver);
    }
}