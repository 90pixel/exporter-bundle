<?php

namespace DIA\ExporterBundle\Routing;

use Symfony\Component\Config\Loader\Loader;

class ExporterLoader extends Loader
{

    public function load($resource, string $type = null)
    {
        // TODO: Implement load() method.
    }

    public function supports($resource, string $type = null)
    {
        dd($type);
    }
}