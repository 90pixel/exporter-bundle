<?php

namespace DIA\ExporterBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("operationName", type="string"),
 *     @Attribute("useExtension", type="bool")
 * )
 */
final class ExporterConfig
{
    public $operationName;

    public $useExtension;
}