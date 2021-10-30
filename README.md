# Exporter Bundle

You can easily export your data to pdf or excel format.

Documentation
=============

* [Getting started](#getting-started)
  * [Prerequisites](#prerequisites)
  * [Installation](#installation)
  * [Configuration](#configuration)
  * [Usage](#usage)
* [Advanced Configuration](#advanced-configuration)
  * [Custom filename](#custom-filename)
  * [Custom twig template](#custom-twig-template)
  * [Custom Exporter Class](#custom-exporter-class)
    * [Advanced filename](#advanced-filename)
    * [Add headers](#add-headers)
    * [Access Query Builder](#access-query-builder)
    * [Manage Filters (Extensions)](#manage-filters-extensions)
  * [Custom Driver](#custom-driver)

Getting started
===============

Prerequisites
-------------
This bundle requires Symfony 5.0+ and api platform.

Installation
------------
```bash
$ composer require 90pixel/exporter-bundle
```

### Register bundle
You can skip this step if you're using flex.
```php
return [
    //...
    DPX\ExporterBundle\ExporterBundle::class => ['all' => true],
];
```

Configuration
-------------
Add custom collection operation in your `entity`.
````php
/**
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "export"={
 *              "method"="GET",
 *              "path"="/products/export"
 *          }
 *     }
 * )
 * ...
 */
````
then register your custom operation.

### Xlsx
```php
/**
 * ...
 * @ExporterConfig(
 *     operationName="export"
 * )
 * ...
 */
```

### Pdf
```php
/**
 * ...
 * @ExporterConfig(
 *     operationName="export",
 *     driver="dpx.exporter.driver.pdf",
 * )
 * ...
 */
```

Usage
-----
Go to ``http://localhost/api/products/export``

If it works, a file named `export.xlsx` will download.

Also you can customize filename.
For advanced usage, please see [advanced configuration](#advanced-configuration) section.

Advanced Configuration
======================

Custom Filename
---------------
```php
/**
 * ...
 * @ExporterConfig(
 *     operationName="export",
 *     filename="products.xlsx"
 * )
 * ...
 */
```

For more advanced filename, please see [advanced filename](#advanced-filename) section.

Custom Twig Template
--------------------
You can customize pdf style easily with your own twig template.
```php
/**
 * ...
 * @ExporterConfig(
 *     operationName="export",
 *     templateName="pdf/products.html.twig"
 * )
 * ...
 */
```

Custom Exporter Class
---------------------
You can customize few things easily with custom exporter class.

Generate exporter class.
```php
<?php
// src/Exporter/ProductExporter.php

namespace App\Exporter;

class ArticleOfferExporter extends ExporterHelper
{
}
```

### Advanced filename
```php
<?php
// src/Exporter/ProductExporter.php

namespace App\Exporter;

class ArticleOfferExporter extends ExporterHelper
{
    /**
     * @return string
     */
    public function getFileName(): string
    {
        return sprintf('products-%s.xlsx', date('Y')); // products-2021.xlsx
    }
}
```

### Add headers
```php
<?php
// src/Exporter/ProductExporter.php

namespace App\Exporter;

class ArticleOfferExporter extends ExporterHelper
{
    public $headers = [
        'ID',
        'Product Name',
        'Barcode',
        'Price',
    ];
}
```

### Access Query Builder
```php
<?php
// src/Exporter/ProductExporter.php

namespace App\Exporter;

class ArticleOfferExporter extends ExporterHelper
{
    // ...
    
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     */
    public function builder(QueryBuilder $queryBuilder, string $alias)
    {
        // Customize query
    }
}
```

### Manage Filters (Extensions)
Sometimes you want to enable some extensions for your export.
You can easily define which extensions will be enabled.

**Note:** API platform query filters are enabled by default.
```php
<?php
// src/Exporter/ProductExporter.php

namespace App\Exporter;

class ArticleOfferExporter extends ExporterHelper
{
    // ...
    
    /**
     * @return string[]
     */
    public function filters(): array
    {
        return [
            FilterExtension::class,
            MyCustomExtension::class
        ];
    }
}
```

Custom Driver
-------------
You can expand output support with custom drivers.
For example, if you want to output .jpg, this section is for you.

The custom driver should look like this.
```php
<?php

namespace DPX\ExporterBundle\Driver;

use DPX\ExporterBundle\Helper\DriverHelper;
use Symfony\Component\HttpFoundation\Response;

class JpgDriver extends DriverHelper
{
    public function handle($data): Response
    {
        // The magic must happen here.
    }
}
```

## Author
[Muhep Atasoy](https://github.com/muhep06)

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details