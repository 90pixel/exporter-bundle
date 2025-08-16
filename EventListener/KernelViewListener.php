<?php

namespace DPX\ExporterBundle\EventListener;

use DPX\ExporterBundle\Manager\ExporterManager;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class KernelViewListener
{
    public function __construct(private readonly ExporterManager $exporterManager)
    {
    }

    public function onKernelView(ViewEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        if (!$attributes->has('_dpx_exporter_operation')) {
            return;
        }

        $config = $attributes->get('_dpx_exporter_operation');
        $this->exporterManager->setOperation($config->operation);

        $controllerResult = $event->getControllerResult();

        $response = $this->exporterManager->getDriver()->handle($controllerResult);
        $event->setResponse($response);
    }
}
