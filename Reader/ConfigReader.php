<?php

namespace DPX\ExporterBundle\Reader;

use DPX\ExporterBundle\Annotation\ExporterConfig;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

class ConfigReader
{
    public static function read(?string $resourceClass, ?string $operationName): ?ExporterConfig
    {
        if (is_null($resourceClass) || is_null($operationName)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($resourceClass);
        $reader = new AnnotationReader();
        $annotations = $reader->getClassAnnotations($reflectionClass);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ExporterConfig && $annotation->operationName === $operationName) {
                return $annotation;
            }
        }

        return null;
    }
}