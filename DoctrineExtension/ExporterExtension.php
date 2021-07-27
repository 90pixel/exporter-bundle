<?php

namespace DIA\ExporterBundle\DoctrineExtension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\FilterExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\ArticleOffer;
use DIA\ExporterBundle\Helper\ExporterHelper;
use DIA\ExporterBundle\Reader\ConfigReader;
use Doctrine\ORM\QueryBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ExporterExtension implements QueryCollectionExtensionInterface
{
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

    public function __construct(
        iterable $collectionExtensions,
        SerializerInterface $serializer,
        RequestStack $requestStack
    )
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->serializer = $serializer;
        $this->request = $requestStack->getCurrentRequest();
        $this->exporter = new ExporterHelper();
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (!$this->supports($resourceClass, $operationName)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $exporterClass = $this->getExporterClass($resourceClass);
        if (class_exists($exporterClass)) {
            $this->exporter = new $exporterClass();
            $this->exporter->builder($queryBuilder, $alias);
        }

        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if (in_array(get_class($extension), [ FilterExtension::class ])) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
            }
        }

        $this->exportExcel($this->getResult($queryBuilder));
    }

    public function supports(string $resourceClass, string $operationName): bool
    {
        $config = ConfigReader::read($resourceClass, $operationName);
        if (!$config) return false;

        return $config->useExtension === true && $config->operationName === $operationName;
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