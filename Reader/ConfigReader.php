<?php

namespace DPX\ExporterBundle\Reader;

use ApiPlatform\Metadata\Operation;
use DPX\ExporterBundle\Annotation\ExporterConfig;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ConfigReader
{
    public static function read(Operation $operation): ?ExporterConfig
    {
        $options = $operation->getExtraProperties();
        if (!isset($options['exporter_config'])) {
            return null;
        }

        $exporterConfig = $options['exporter_config'];
        $exporterConfig['operation'] = $operation;

        return self::populate($exporterConfig);
    }

    private static function populate(array $exporterConfig): ExporterConfig
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $config = new ExporterConfig();

        foreach ($exporterConfig as $key => $value) {
            if ($accessor->isWritable($config, $key)) {
                $accessor->setValue($config, $key, $value);
            }
        }

        return $config;
    }
}
