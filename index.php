<?php

$options = getopt("o:f:t::");

$op = $options['o'];
$filename = isset($options['f']) ? $options['f'] : "tmp/italy.osm.bz2";
$tagslist = isset($options['t']) ? $options['t'] : null;

if(!file_exists($filename)) {
    die("`$filename` not exists\n");
}
if($tagslist && !file_exists($tagslist)) {
    die("$tagslist not exists\n");
}

// ----------------------------------------

require "./vendor/autoload.php";

use Osm\Parser\OsmParser;

switch($op) {
    case "tags":
        extractTags($filename);
        break;
    case "export":
        break;
    default:
        die("command not recognized.");
}

function extractTags($filename) {

    $parser = new OsmParser($filename);
    $parser->setHandlers(["Osm\Parser\Handler\TagsHandler" => [ "tmp/tags.txt" ]]);

    $parser->parse();

}

//$tagslist = file_get_contents($tagslist);
//$parser->useTags($tags);
