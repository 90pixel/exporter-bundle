<?php

namespace DIA\ExporterBundle\Helper;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\FilterExtension;
use DIA\ExporterBundle\Interfaces\ExporterInterface;
use Doctrine\ORM\QueryBuilder;

class ExporterHelper implements ExporterInterface
{
    public $headers = [];

    public function builder(QueryBuilder $queryBuilder, string $alias)
    {
        // TODO: Implement builder() method.
    }

    public function row(array $data): array
    {
        return $data;
    }

    public function filters(): array
    {
        return [
            FilterExtension::class
        ];
    }
}