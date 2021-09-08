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
}