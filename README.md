OsmParser
=========

PHP classes to parse OpenStreetMap .osm files

This library is build to be run on CLI (as it could require long time to run)


NOTE: this class is a stub and need more work on it. At the moment all parsed data are kept in memory which could (and will!) cause the system to hang.


Usage
------

```php

$filename='trentino-alto-adige.osm.bz2';

$parser = new WayParser($filename);
$parser->parse();
$dataset = $parser->getDataset();

var_dump($dataset);

```
