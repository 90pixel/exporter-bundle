<?php

namespace DIA\ExporterBundle\Interfaces;

use Symfony\Component\HttpFoundation\Response;

interface DriverInterface
{
    public function handle($data): Response;
}