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

    public function __construct()
    {
        $this->type = $this->type ?? 'excel';
    }
}