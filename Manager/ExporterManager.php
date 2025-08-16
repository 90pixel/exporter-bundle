<?php

namespace DPX\ExporterBundle\Manager;

use ApiPlatform\Metadata\Operation;
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
    private ?Operation $operation = null;

    /**
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param Operation $operation
     * @return void
     */
    public function setOperation(Operation $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return Operation|null
     */
    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    /**
     * @return ExporterConfig|null
     */
    public function getConfig(): ?ExporterConfig
    {
        return ConfigReader::read($this->getOperation());
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
