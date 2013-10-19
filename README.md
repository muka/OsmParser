OsmParser
=========

PHP classes to parse OpenStreetMap .osm files

This library is build to be run on CLI (as it could require long time to run)


NOTE: this class is a stub and need more work on it. At the moment all parsed data are kept in memory which could (and will!) cause the system to hang.

Install
------
In your composer.json add something like
```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/muka/XmlStreamer.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/muka/OsmParser"
        }
    ],
    "require": {

        ... other require ...

        "muka/OsmParser": "dev-master as 1.0"
    }

```
and `composer update` then

Usage
------

```php

$filename='trentino-alto-adige.osm.bz2';

$parser = new WayParser($filename);
$parser->parse();
$dataset = $parser->getDataset();

var_dump($dataset);

```
