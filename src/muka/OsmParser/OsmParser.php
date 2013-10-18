<?php

namespace muka\OsmParser;
use Osm\Streamer\BzipXmlStreamer;

abstract class OsmParser extends BzipXmlStreamer {

    protected $ids = [];
    protected $dataset = [];
    protected $keysMap = [];

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


    protected function addElement($type, $elem) {
        $this->dataset[$type][$elem->meta['id']] = $elem;
    }

    protected function getElements($type) {
        if(!isset($this->dataset[$type])) {
            $this->dataset[$type] = [];
        }
        return $this->dataset[$type];
    }

    protected function setElements($type, $elements) {
        if(!isset($this->dataset[$type])) {
            $this->dataset[$type] = [];
        }
        $this->dataset[$type] = $elements;
    }

    public function setDataset($ds) {
        $this->dataset = $ds;
    }

    public function getDataset() {
        return $this->dataset;
    }

    public function getWays() {
        return $this->getElements("ways");
    }

    public function setWays($items) {
        $this->setElements("ways", $items);
    }

    public function setNodes($nodes) {
        $this->getElements("nodes", $nodes);
    }

    public function getNodes() {
        return $this->getElements("nodes");
    }

    protected function getRelations() {
        return $this->getElements("relations");
    }

    protected function setRelations($r) {
        $this->getElements("relations", $r);
    }

    protected function addWay($item) {
        $this->addElement("ways", $item);
    }

    protected function addNode($node) {
        $this->addElement("nodes", $node);
    }

    protected function addRelation($relation) {
        $this->addElement("relations", $relation);
    }


}