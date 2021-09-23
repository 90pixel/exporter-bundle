<?php

namespace DPX\ExporterBundle\Interfaces;

use Symfony\Component\HttpFoundation\Response;

interface DriverInterface
{
    public function handle($data): Response;
}