<?php

namespace DIA\ExporterBundle\Reader;

use DIA\ExporterBundle\Annotation\ExporterConfig;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

class ConfigReader
{
    public static function read(string $resourceClass, string $operationName): ?ExporterConfig
    {
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