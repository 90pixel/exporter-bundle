<?php

namespace DIA\ExporterBundle\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DIA\ExporterBundle\Helper\DriverHelper;
use DIA\ExporterBundle\Helper\ExporterHelper;
use DIA\ExporterBundle\Interfaces\ExporterInterface;
use DIA\ExporterBundle\Reader\ConfigReader;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

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
     * @var Request|null
     */
    private $request;

    /**
     * @var ExporterHelper
     */
    private $exporter;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ExporterDataProvider constructor.
     * @param EntityManagerInterface $entityManager
     * @param iterable $collectionExtensions
     * @param RequestStack $requestStack
     * @param Environment $twig
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $collectionExtensions,
        RequestStack $requestStack,
        Environment $twig,
        ContainerInterface $container
    )
    {
        $this->entityManager = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
        $this->request = $requestStack->getCurrentRequest();
        $this->twig = $twig;
        $this->container = $container;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->entityManager->getRepository($resourceClass)->createQueryBuilder('o');

        $this->exporter = $this->getExporterClass($resourceClass, $operationName);

        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if (in_array(get_class($extension), $this->exporter->filters())) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            }
        }

        $this->exporter->builder($queryBuilder, 'o');
        $results = $this->exporter->getResult($queryBuilder);

        $rows = [];
        foreach ($results as $row) {
            $rows[] = $this->exporter->row($row);
        }

        return $rows;
    }

    /**
     * @param string $resourceClass
     * @param string|null $operationName
     * @param array $context
     * @return bool
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $config = ConfigReader::read($resourceClass, $operationName);
        if (!$config) return false;

        return $config->operationName === $operationName;
    }

    /**
     * @param string $resourceClass
     * @param string $operationName
     * @return ExporterInterface
     */
    private function getExporterClass(string $resourceClass, string $operationName): ExporterInterface
    {
        $config = ConfigReader::read($resourceClass, $operationName);

        $exporterClass = $this->container->get(ExporterHelper::class);
        if ($config->exporterClass) {
            $exporterClass = $this->container->get($config->exporterClass);
        }

        $exporterClass->setConfig($config);

        return $exporterClass;
    }


    /*private function exportExcel($data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rows[] = $this->exporter->headers;
        foreach ($data as $row) {
            $rows[] = $this->exporter->row($row);
        }

        $sheet->fromArray(array_values($rows), null, 'A1', true);

        // Auto width
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell) {
            $sheet->getCell($cell->getColumn() . '1')->getStyle()->getFont()->setBold(true);

            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        // Filter
        $sheet->setAutoFilter($spreadsheet->getActiveSheet()
            ->calculateWorksheetDimension());

        $writer = new Xlsx($spreadsheet);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s"', $filename));
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->prepare($this->request);
        $response->sendHeaders();
        $writer->save('php://output');
        exit();
    }*/

    private function exportPdf($data, string $templateName, string $filename)
    {
        $rows = [];
        foreach ($data as $row) {
            $rows[] = $this->exporter->row($row);
        }

        $htmlOutput = $this->twig->render($templateName, [
            'headers' => $this->exporter->headers,
            'results' => $rows
        ]);
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($htmlOutput);
        $mpdf->Output();
        exit();
    }
}