<?php

namespace DPX\ExporterBundle\Annotation;

use ApiPlatform\Metadata\Operation;
use DPX\ExporterBundle\Constant\DriverConstant;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class ExporterConfig
{
    /**
     * @var string
     */
    public $operationName;

    /**
     * @var Operation|null
     */
    public ?Operation $operation;

    /**
     * @var string
     */
    public $exporterClass;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $templateName;

    /**
     * @var string
     */
    public $driver;

    /**
     * @var array
     */
    public $headers = [];

    public function __construct()
    {
        $this->driver = $this->driver ?? DriverConstant::XLSX;
        $this->filename = $this->driver === DriverConstant::XLSX ? 'export.xlsx' : 'export.pdf';
        $this->templateName = '@Exporter/pdf/default.html.twig';
    }
}
