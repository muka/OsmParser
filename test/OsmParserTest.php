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
        $path = realpath("./test/data/test.bz2");
        return $path;
    }

    private function loadParser() {
        $filepath = $this->getFilePath();
        $this->parser = new OsmParser($filepath);
    }

    /**
     * @expectedException muka\OsmParser\Exception\OsmParserException
     */
    public function testFailOpen() {
        $filepath = "non_existing_file.bz2";
        $this->parser = new OsmParser($filepath);
    }

    public function testXmlNodes() {

        $this->loadParser();

        $testCase = $this;
        $this->parser->getDispatcher()->addListener('osm_parser.item.node', array($this, 'isNode'));
        $this->parser->getDispatcher()->addListener('osm_parser.item.relation', array($this, 'isRelation'));
        $this->parser->getDispatcher()->addListener('osm_parser.item.way', array($this, 'isWay'));
        $this->parser->getDispatcher()->addListener('osm_parser.item.bounds', array($this, 'isBounds'));

        $this->parser->parse();

    }

    protected function is($event, $type) {
        $this->assertTrue($event->getType() === $type);
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

}
