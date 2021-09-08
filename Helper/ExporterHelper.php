<?php

namespace DIA\ExporterBundle\Helper;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\FilterExtension;
use DIA\ExporterBundle\Interfaces\ExporterInterface;
use Doctrine\ORM\QueryBuilder;

class ExporterHelper implements ExporterInterface
{
    public $headers = [];

    public $normalize = true;

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
     * @return array
     */
    public function supportNormalization(): array
    {
        return [
            'format' => null,
            'context' => []
        ];
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
}