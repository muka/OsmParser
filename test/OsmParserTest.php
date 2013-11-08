<?php

namespace muka\OsmParser\Test;

use muka\OsmParser\OsmParser;
use muka\OsmParser\Event\OsmParserItemEvent;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OsmParserTest extends \PHPUnit_Framework_TestCase
{

    protected $parser;

    private function getFilePath() {
        $path = realpath("./test/data/test.txt");
        return $path;
    }

    public function testXmlNodes() {

        $file = fopen($this->getFilePath(), 'r');
        $this->parser = new OsmParser($file);

        $this->parser->getDispatcher()->addListener('osm_parser.item', array($this, 'nodeHasTag'));
        $this->parser->getDispatcher()->addListener('osm_parser.item', array($this, 'relationHasMemebers'));
        $this->parser->getDispatcher()->addListener('osm_parser.item', array($this, 'wayHasNd'));
        $this->parser->getDispatcher()->addListener('osm_parser.item', array($this, 'wayHasTag'));
        $this->parser->getDispatcher()->addListener('osm_parser.item', array($this, 'boundHasLatLon'));

        $this->parser->parse();
    }

    protected function is($event, $type) {
        return ($event->getType() === $type);
    }

    public function isNode(OsmParserItemEvent $event) {
        $this->is($event, 'node');
    }

    public function isRelation(OsmParserItemEvent $event) {
        $this->is($event, 'relation');
    }

    public function isWay(OsmParserItemEvent $event) {
        $this->is($event, 'way');
    }

    public function isBounds(OsmParserItemEvent $event) {
        $this->is($event, 'bounds');
    }

    public function relationHasMemebers(OsmParserItemEvent $event) {

        if(!$this->isRelation($event)) {
            return;
        }

        $item = $event->getItem();
        $this->assertTrue($item->members[0]['ref'] == 200);
    }

    public function wayHasNd(OsmParserItemEvent $event) {

        if(!$this->isWay($event)) {
            return;
        }

        $item = $event->getItem();
        $this->assertTrue($item->refs[0] == 100);
    }

    public function boundHasLatLon(OsmParserItemEvent $event) {

        if(!$this->isBounds($event)) {
            return;
        }

        $item = $event->getItem();
        $this->assertTrue($item->meta['minlat'] == 45.61887);
    }

    public function wayHasTag(OsmParserItemEvent $event) {

        if(!$this->isWay($event)) {
            return;
        }

        $item = $event->getItem();
        $this->assertTrue($item->tags['ref'] == 'SS48');
    }

    public function nodeHasTag(OsmParserItemEvent $event) {

        if(!$this->isNode($event)) {
            return;
        }

        $item = $event->getItem();
        if($item->meta['id'] == 500) {
            $this->assertTrue($item->tags['created_by'] == 'asd');
        }
    }

}
