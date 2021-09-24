<?php

namespace DPX\ExporterBundle\Annotation;

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

    public function __construct()
    {
        $this->driver = $this->driver ?? 'dpx.exporter.driver.xlsx';
    }
}