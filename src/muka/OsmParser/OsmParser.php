<?php

namespace muka\OsmParser;

use Hobnob\XmlStreamReader\Parser;
use Symfony\Component\EventDispatcher\EventDispatcher;


class OsmParser extends Parser {

    protected $resource;
    protected $chunkSize;
    protected $totalBytes;

    protected $dispatcher;
    protected $continue = true;
    protected $completed = false;

    public function __construct($data, $chunkSize = 1024) {

        $this->setResource($data, $chunkSize);

        $this->dispatcher = new EventDispatcher();

        $this->getDispatcher()->addListener("osm_parser.process.stop", array($this, "stop"));
        $this->getDispatcher()->addListener("osm_parser.process.completed", array($this, "completed"));

        $this->registerCallbacks([
            ['/osm/bounds/', [$this, "handleBounds"]],
            ['/osm/node/', [$this, "handleNode"]],
            ['/osm/way/', [$this, "handleWay"]],
            ['/osm/relation/', [$this, "handleRelation"]],
        ]);

    }

    protected function setResource($data, $chunkSize) {

        //Ensure that the $data var is of the right type
        if ( !is_string( $data )
            && ( !is_resource( $data ) || get_resource_type($data) !== 'stream' )
        )
        {
            throw new Exception\OsmParserException( 'Data must be a string or a stream resource' );
        }
        $this->resource = $data;

        //Ensure $chunkSize is the right type
        if ( !is_int( $chunkSize ) )
        {
            throw new Exception\OsmParserException( 'Chunk size must be an integer' );
        }

        $this->chunkSize = $chunkSize;
    }

    protected function handleBounds(Parser $parser, \SimpleXMLElement $xml) {
        $this->dispatch('bounds', $this->getBound($parser, $xml));
    }
    protected function handleNode(Parser $parser, \SimpleXMLElement $xml) {
        $this->dispatch('node', $this->getNode($parser, $xml));
    }
    protected function handleWay(Parser $parser, \SimpleXMLElement $xml) {
        $this->dispatch('way', $this->getWay($parser, $xml));
    }
    protected function handleRelation(Parser $parser, \SimpleXMLElement $xml) {
        $this->dispatch('relation', $this->getRelation($parser, $xml));
    }

    public function parse($data = null, $chunkSize = null)
    {
        if(is_null($data)) {
            $data = $this->resource;
        }

        if(is_null($chunkSize)) {
            $chunkSize = $this->chunkSize;
        }

        $this->setResource($data, $chunkSize);

        parent::parse($this->resource, $this->chunkSize);
        $this->dispatcher->dispatch("osm_parser.process.completed");
        return $this;
    }

    public function getDispatcher() {
        return $this->dispatcher;
    }

    public function isStopped() {
        return $this->continue;
    }

    public function isCompleted() {
        return $this->completed;
    }

    public function stop() {
        $this->stopParsing();
        $this->continue = false;
    }

    public function completed() {
        $this->completed = true;
    }

    public function getStatus() {
        return [
            "size"       => $this->getTotalBytes(),
            "position"   => $this->getReadBytes(),
            "percentage" => ($this->getReadBytes() * 100) / $this->getTotalBytes(),
        ];
    }

    protected function getTotalBytes() {
        if(!isset($this->totalBytes)) {
            $stat = fstat($this->resource);
            $this->totalBytes = $stat['size'];
        }
        return $this->totalBytes;
    }

    protected function getReadBytes() {
        return ftell($this->resource);
    }

    protected function dispatch($elementName, $data) {
        $this->dispatcher->dispatch('osm_parser.item', new Event\OsmParserItemEvent($elementName, $data, $this));
    }

    protected function getNode(Parser $parser, \SimpleXMLElement $xml) {

        $node = $this->newElement();
        $node->meta = $this->getMeta($xml);
        $node->tags = $this->getTags($xml);

        return $node;
    }

    protected function getBound(Parser $parser, \SimpleXMLElement $xml) {

        // do it once
        $bounds = $this->newElement();
        $bounds->meta = $this->toArray($xml);
        unset($bounds->meta['id']);

        return $bounds;
    }

    protected function getWay(Parser $parser, \SimpleXMLElement $xml) {

        $item = $this->newElement();
        $item->meta = $this->getMeta($xml);
        $item->tags = $this->getTags($xml);
        $item->refs = $this->getNodesRef($xml);

        return $item;
    }

    protected function getRelation(Parser $parser, \SimpleXMLElement $xml) {

        $relation = $this->newElement();
        $relation->meta = $this->getMeta($xml);
        $relation->members = $this->getMembers($xml);
        $relation->tags = $this->getTags($xml);

        return $relation;
    }

    protected function getMembers($xml) {
        $members = [];

        foreach($xml->member as $member) {
            $member = $this->toArray($member);
            $members[] = $member;
        }

        return $members;
    }

    protected function toArray($elem) {
        $values = [];
        $attr = $elem->attributes();
        foreach($attr as $k => $v) {
            $values[$k] = (string)$v;
        }
        return $values;
    }

    protected function getMeta($xml) {
        return $this->toArray($xml);
    }

    protected function getNodesRef($xml) {
        $list = [];

        if($xml->nd) {
            foreach ($xml->nd as $ndNode) {
                $nd = $this->toArray($ndNode);
                $list[] = $nd['ref'];
            }
        }
        return $list;
    }

    protected function getTags($xml) {
        $tags = [];
        if(isset($xml->tag)) {
            foreach ($xml->tag as $tagNode) {
                $tag = $this->toArray($tagNode);
                $tags[ (string)$tag['k'] ] = (string)$tag['v'];
            }
        }
        return $tags;
    }

    protected function newElement() {
        $el = new \stdClass();
        $el->meta = array();
        return $el;
    }

}