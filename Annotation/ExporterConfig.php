<?php

namespace DIA\ExporterBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("operationName", type="string"),
 * )
 */
final class ExporterConfig
{
    public $operationName;
}