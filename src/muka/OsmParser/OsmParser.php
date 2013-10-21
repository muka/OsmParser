<?php

namespace muka\OsmParser;

use muka\OsmParser\Streamer\BzipXmlStreamer;

use Symfony\Component\EventDispatcher\EventDispatcher;

class OsmParser extends BzipXmlStreamer {

    protected $ids = [];
    protected $dataset = [];
    protected $keysMap = [];

    protected $dispatcher;

    public function __construct($mixed, $chunkSize = 16384, $customRootNode = null, $totalBytes = null, $customChildNode = null) {
        $this->dispatcher = new EventDispatcher();
        parent::__construct($mixed, $chunkSize, $customRootNode, $totalBytes, $customChildNode);
    }

    public function getDispatcher() {
        return $this->dispatcher;
    }

    public function processNode($xmlString, $elementName, $nodeIndex) {

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
            $this->dispatcher->dispatch('osm_parser.item', new Event\OsmParserItemEvent($elementName, $data));
            $this->dispatcher->dispatch('osm_parser.item.'.$elementName, new Event\OsmParserItemEvent($elementName, $data));
        }

        return true;
    }

    protected function getNode($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);

        $node = $this->newElement();
        $node->meta = $this->getMeta($xml);
        $node->tag = $this->getTags($xml);

        return $node;
    }

    protected function getBound($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);

        // do it once
        $bounds = $this->newElement();
        $bounds->meta = $this->toArray($xml);
        $bounds->meta['id'] = 0;

        return $bounds;
    }

    protected function getWay($xmlString, $elementName, $nodeIndex) {

        $xml = simplexml_load_string($xmlString);
//
//        $found = false;
//        foreach ($xml->tag as $tag) {
//            $key = (string)$tag['k'];
//            $val = (string)$tag['v'];
//            if($found = $this->findKey($key, $val)) {
//                break;
//            }
//        }
//
//        if(!$found) {
//            return true;
//        }

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
            $members[$member['ref']] = $member;
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
        if($xml->tag) {
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