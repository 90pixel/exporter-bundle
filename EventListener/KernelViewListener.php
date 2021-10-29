<?php

namespace DPX\ExporterBundle\EventListener;

use DPX\ExporterBundle\Manager\ExporterManager;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class KernelViewListener
{
    /**
     * @var ExporterManager
     */
    private $exporterManager;

    public function __construct(ExporterManager $exporterManager)
    {
        $this->exporterManager = $exporterManager;
    }

    public function onKernelView(ViewEvent $event)
    {
        $controllerResult = $event->getControllerResult();
        $config = $this->exporterManager->getConfig();
        $operationName = $this->exporterManager->getOperationName();

        if (!$config || !$operationName) {
            return;
        }

        if ($config->operationName !== $operationName) {
            return;
        }

        $response = $this->exporterManager->getDriver()->handle($controllerResult);
        $event->setResponse($response);
    }
}