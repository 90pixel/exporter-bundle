<?php

namespace DIA\ExporterBundle\Interfaces;

use Doctrine\ORM\QueryBuilder;

interface ExporterInterface
{
    public function builder(QueryBuilder $queryBuilder, string $alias);

    public function filters(): array;
}