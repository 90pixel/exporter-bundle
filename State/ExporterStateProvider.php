<?php

namespace DPX\ExporterBundle\State;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use DPX\ExporterBundle\Annotation\ExporterConfig;
use DPX\ExporterBundle\Exception\ExporterOptionsNotProvidedException;
use DPX\ExporterBundle\Interfaces\ExporterInterface;
use DPX\ExporterBundle\Manager\ExporterManager;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Serializer\SerializerInterface;

class ExporterStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ExporterManager $exporterManager,
        private readonly SerializerInterface $serializer,
        #[TaggedIterator('api_platform.doctrine.orm.query_extension.collection')]
        private readonly iterable $collectionExtensions,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->exporterManager->setOperation($operation);
        $config = $this->exporterManager->getConfig();

        if (!$config) {
            throw new ExporterOptionsNotProvidedException(
                'Exporter options are not provided. Please ensure that the operation is configured with ExporterConfig.'
            );
        }

        $resourceClass = $operation->getClass();
        $exporter = $this->exporterManager->getExporter();

        $this->injectAttributes($config);

        $queryBuilder = $this->createQueryBuilder($resourceClass);
        $this->applyAllowedCollectionExtensions(
            $exporter,
            $queryBuilder,
            $resourceClass,
            $operation,
            $context
        );

        $exporter->builder($queryBuilder, 'o');
        $results = $exporter->getResult($queryBuilder);

        if (method_exists($exporter, 'row')) {
            $controllerResult = [];
            foreach ($results as $result) {
                $controllerResult[] = $exporter->row($result);
            }

            return $controllerResult;
        }

        return $this->serializer->normalize($results, null, $context);
    }

    private function createQueryBuilder(?string $resourceClass): QueryBuilder
    {
        return $this->entityManager
            ->getRepository($resourceClass)
            ->createQueryBuilder('o');
    }

    private function applyAllowedCollectionExtensions(
        ExporterInterface $exporter,
        QueryBuilder $queryBuilder,
        $resourceClass,
        Operation $operation,
        array $context
    ): void
    {
        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if (in_array(get_class($extension), $exporter->filters())) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);
            }
        }
    }

    private function injectAttributes(ExporterConfig $config): void
    {
        $request = $this->exporterManager->getRequest();
        $request->attributes->set('_dpx_exporter_operation', $config);
    }
}
