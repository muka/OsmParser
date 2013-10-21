OsmParser
=========

PHP classes to parse OpenStreetMap .osm files

This library is supposed to run on CLI (as it could require long time to run)


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

use muka\OsmParser\OsmParser;
use muka\OsmParser\Event\OsmParserItemEvent;

$path = realpath("./tmp/trentino-alto-adige.osm.bz2");
$parser = new OsmParser($path);

// Register to event `osm_parser.item`
$parser->getDispatcher()->addListener("osm_parser.item", function($e) {
    var_dump($e->getType());
    var_dump($e->getItem());
});

/**
* Event can be either one of the parent nodes available in an xml
* - osm_parser.item.node
* - osm_parser.item.relation
* - osm_parser.item.way
* - osm_parser.item.bounds
*/
$parser->getDispatcher()->addListener("osm_parser.item.node", function($e) {
    var_dump($e->getType());
    var_dump($e->getItem());
});

$parser->parse();

```
