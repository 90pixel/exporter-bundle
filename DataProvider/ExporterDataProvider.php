<?php

namespace DIA\ExporterBundle\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DIA\ExporterBundle\Helper\ExporterHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
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
     * @var Request|null
     */
    private $request;

    /**
     * @var ExporterHelper
     */
    private $exporter;

    /**
     * ExporterDataProvider constructor.
     * @param EntityManagerInterface $entityManager
     * @param iterable $collectionExtensions
     * @param SerializerInterface $serializer
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $collectionExtensions,
        SerializerInterface $serializer,
        RequestStack $requestStack
    )
    {
        $this->entityManager = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
        $this->serializer = $serializer;
        $this->request = $requestStack->getCurrentRequest();
        $this->exporter = new ExporterHelper();
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->entityManager->getRepository($resourceClass)->createQueryBuilder('o');

        $exporterClass = $this->getExporterClass($resourceClass);
        if (class_exists($exporterClass)) {
            $this->exporter = new $exporterClass();
            $this->exporter->builder($queryBuilder, 'o');
        }

        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if (in_array(get_class($extension), $this->exporter->filters())) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            }
        }

        $this->exportExcel($this->getResult($queryBuilder));
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $operationName == 'export';
    }

    private function getExporterClass(string $resourceClass): string
    {
        $resourceClass = str_replace('Entity', 'Exporter', $resourceClass);
        return $resourceClass . 'Exporter';
    }

    private function getResult(QueryBuilder $queryBuilder)
    {
        $results = $this->exporter->getResult($queryBuilder);
        if ($this->exporter->normalize) {
            $settings = $this->exporter->supportNormalization();
            $format = $settings['format'] ?? null;
            $context = $settings['context'] ?? [];
            $results = $this->serializer->normalize($results, $format, $context);
        }

        return $results;
    }

    private function exportExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rows[] = $this->exporter->headers;
        foreach ($data as $row) {
            $rows[] = $this->exporter->row($row);
        }

        $sheet->fromArray(array_values($rows), null, 'A1', true);
        $writer = new Xlsx($spreadsheet);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s"', 'dia-export.xlsx'));
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->prepare($this->request);
        $response->sendHeaders();
        $writer->save('php://output');
        exit();
    }
}