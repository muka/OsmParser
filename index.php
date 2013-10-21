<?php

require "./test/bootstrap.php";

use muka\OsmParser\OsmParser;
use muka\OsmParser\Event\OsmParserItemEvent;

$path = './test/data/test.bz2';
$parser = new OsmParser($path);

$parser->getDispatcher()->addListener("osm_parser.item", function($e) {
    var_dump($e->getType());
    var_dump($e->getItem());
});

$parser->parse();
