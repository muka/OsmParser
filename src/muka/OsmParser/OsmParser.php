<?php

namespace muka\OsmParser;

use muka\OsmParser\Streamer\BzipXmlStreamer;

use Symfony\Component\EventDispatcher\EventDispatcher;

class OsmParser extends BzipXmlStreamer {

    protected $dispatcher;
    protected $continue = true;
    protected $completed = false;

    public function __construct($mixed, $chunkSize = 16384, $customRootNode = null, $totalBytes = null, $customChildNode = null) {

        $this->dispatcher = new EventDispatcher();

        $this->getDispatcher()->addListener("osm_parser.process.stop", array($this, "stop"));
        $this->getDispatcher()->addListener("osm_parser.process.completed", array($this, "completed"));

        parent::__construct($mixed, $chunkSize, $customRootNode, $totalBytes, $customChildNode);
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
        $this->continue = false;
    }

    public function completed() {
        $this->completed = true;
    }

    public function getStatus() {
        return [
            "size"       => $this->getTotalBytes(),
            "position"   => $this->getReadBytes(),
            "percentage" => ($this->getTotalBytes() / $this->getReadBytes()) / 0.1,
        ];
    }

    public function processNode($xmlString, $elementName, $nodeIndex) {

        if(!$this->continue) {
            return false;
        }

        $data = null;
        switch($elementName) {
            case "bounds":
                $data = $this->getBound($xmlString, $elementName, $nodeIndex);
                break;
            case "node":
                $data = $this->getNode($xmlString, $elementName, $nodeIndex);
                break;
            case "relation":
                $data = $this->getRelation($xmlString, $elementName, $nodeIndex);
                break;
            case "way":
                $data = $this->getWay($xmlString, $elementName, $nodeIndex);
                break;
        }

        if($data) {
            $parserItem = new Event\OsmParserItemEvent($elementName, $data, $this);
            $this->dispatcher->dispatch('osm_parser.item', $parserItem);
            $this->dispatcher->dispatch('osm_parser.item.'.$elementName, $parserItem);
            $parserItem = null;
        }

        return true;
    }

    protected function getNode($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);

        $node = $this->newElement();
        $node->meta = $this->getMeta($xml);
        $node->tags = $this->getTags($xml);

        return $node;
    }

    protected function getBound($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);

        // do it once
        $bounds = $this->newElement();
        $bounds->meta = $this->toArray($xml);
        unset($bounds->meta['id']);

        return $bounds;
    }

    protected function getWay($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);

        $item = $this->newElement();
        $item->meta = $this->getMeta($xml);
        $item->tags = $this->getTags($xml);
        $item->refs = $this->getNodesRef($xml);

        return $item;
    }

    protected function getRelation($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);

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


    protected function readNextChunk() {

        $return = parent::readNextChunk();

        if(!$return) {
            $this->getDispatcher()->dispatch("osm_parser.process.completed");
        }

        return $return;
    }

}