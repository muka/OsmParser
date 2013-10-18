<?php

require "./vendor/autoload.php";

use muka\OsmParser\WayParser;
use muka\OsmParser\NodeParser;
use muka\OsmParser\RelationParser;

//$filename = 'tmp/italy.osm.bz2';
$filename = 'tmp/trentino-alto-adige.osm.bz2';
$dataset = parse($filename);

function parse($filename) {

    $timer = time();
    echo sprintf("Started on %s\n", date("Y-m-d H:i", $timer));

    $print_timeElapsed = function($items = null) use($timer) {
        $now = time();
        $take = ($now - $timer);
        if($items) {
            echo sprintf("\n\nFound %s items\n", count($items));
        }
        echo sprintf("Finished on %s\n", date("Y-m-d H:i", $now));
        echo sprintf("Took %s.%s minutes \n", intval($take / 60), intval($take % 60));
    };

    register_shutdown_function($print_timeElapsed);

    $cache = 'tmp/dataset.tmp';

    $dataset = [];
    if(file_exists($cache)) {
        $dataset = unserialize(file_get_contents($cache));
    }

    if(!isset($dataset['ways'])) {
        print "Parse way\n";
        $streamerWay = new WayParser($filename);
        $streamerWay->setDataset($dataset);
        $streamerWay->parse();
        $dataset = $streamerWay->getDataset();
        file_put_contents($cache, serialize($dataset));
        $streamerWay = null;

        $print_timeElapsed($dataset['ways']);
    }

    if(!isset($dataset['nodes'])) {
        print "Parse nodes\n";
        $streamerNode = new NodeParser($filename);
        $streamerNode->setDataset($dataset);
        $streamerNode->parse();
        $dataset = $streamerNode->getDataset();
        file_put_contents($cache, serialize($dataset));
        $streamerNode = null;

        $print_timeElapsed($dataset['nodes']);
    }

    if(!isset($dataset['relations'])) {
        print "Parse relations\n";
        $streamerRelation = new RelationParser($filename);
        $streamerRelation->setDataset($dataset);
        $streamerRelation->parse();
        $dataset = $streamerRelation->getDataset();
        file_put_contents($cache, serialize($dataset));
        $streamerRelation = null;

        $print_timeElapsed($dataset['relations']);
    }

    $total = 0;
    foreach($dataset as $dt) {
        $total += count($dt);
    }

    print sprintf("Found %s items total\n", $total);

    return $dataset;
}

//
//$dataset['ways'] = array_filter($dataset['ways'], function($item) {
//    $keep = true;
//
////    // skip parking
////    if(isset($item->tags['amenity']) && $item->tags['amenity'] == 'parking') {
////        $keep = false;
////    }
////
////    // skip lift
////    if(isset($item->tags['aerialway'])) {
////        $keep = false;
////    }
//
//    return $keep;
//});
//
//array_map(function($item) {
//
//    print "\n==============\n";
//    foreach($item->tags as $k => $v) {
//        printf("%s: %s\n", $k, $v);
//    }
//
//}, $dataset['ways']);