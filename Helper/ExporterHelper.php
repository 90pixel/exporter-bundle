<?php

namespace DPX\ExporterBundle\Helper;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\FilterExtension;
use DPX\ExporterBundle\Annotation\ExporterConfig;
use DPX\ExporterBundle\Interfaces\ExporterInterface;
use Doctrine\ORM\QueryBuilder;

class ExporterHelper implements ExporterInterface
{
    public $headers = [];

    /**
     * @var ExporterConfig
     */
    private $config;

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     */
    public function builder(QueryBuilder $queryBuilder, string $alias)
    {
        // TODO: Implement builder() method.
    }

    public function getResult(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param mixed $data
     * @return array
     */
    public function row($data): array
    {
        return $data;
    }

    /**
     * @return string[]
     */
    public function filters(): array
    {
        return [
            FilterExtension::class
        ];
    }

    public function getFileName(): string
    {
        return $this->config->filename ?? 'export.xlsx';
    }

    /**
     * @param ExporterConfig $config
     */
    public function setConfig(ExporterConfig $config): void
    {
        $this->config = $config;
    }
}