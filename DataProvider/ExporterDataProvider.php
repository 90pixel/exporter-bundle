<?php

namespace DPX\ExporterBundle\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DPX\ExporterBundle\Manager\ExporterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ExporterDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var iterable
     */
    private $collectionExtensions;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ExporterManager
     */
    private $exporterManager;

    /**
     * ExporterDataProvider constructor.
     * @param EntityManagerInterface $entityManager
     * @param iterable $collectionExtensions
     * @param SerializerInterface $serializer
     * @param ExporterManager $exporterManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $collectionExtensions,
        SerializerInterface $serializer,
        ExporterManager $exporterManager
    )
    {
        $this->entityManager = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
        $this->serializer = $serializer;
        $this->exporterManager = $exporterManager;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->entityManager->getRepository($resourceClass)->createQueryBuilder('o');

        $exporter = $this->exporterManager->getExporter();

        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if (in_array(get_class($extension), $exporter->filters())) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            }
        }

        $exporter->builder($queryBuilder, 'o');
        $results = $exporter->getResult($queryBuilder);

        return $this->serializer->normalize($results, null, $context);
    }

    /**
     * @param string $resourceClass
     * @param string|null $operationName
     * @param array $context
     * @return bool
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $config = $this->exporterManager->getConfig();
        if (!$config) return false;

        return $config->operationName === $operationName;
    }
}
