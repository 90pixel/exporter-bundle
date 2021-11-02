<?php

namespace DPX\ExporterBundle\Helper;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\FilterExtension;
use DPX\ExporterBundle\Annotation\ExporterConfig;
use DPX\ExporterBundle\Interfaces\ExporterInterface;
use Doctrine\ORM\QueryBuilder;

class ExporterHelper implements ExporterInterface
{
    /**
     * @var array
     */
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

    /**
     * @param QueryBuilder $queryBuilder
     * @return array
     */
    public function getResult(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder->getQuery()->getResult();
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

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->config->filename;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        if (!count($this->headers)) {
            $this->headers = $this->config->headers;
        }

        return $this->headers;
    }

    /**
     * @param ExporterConfig $config
     */
    public function setConfig(ExporterConfig $config): void
    {
        $this->config = $config;
    }
}