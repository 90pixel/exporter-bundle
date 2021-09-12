<?php

namespace DIA\ExporterBundle\Annotation;

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
    public $type;

    /**
     * @var string
     */
    public $templateName;

    public function __construct()
    {
        $this->type = $this->type ?? 'excel';
    }
}