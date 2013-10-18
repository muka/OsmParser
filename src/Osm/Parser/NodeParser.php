<?php

namespace Osm\Parser;

class NodeParser extends OsmParser {

    public function processNode($xmlString, $elementName, $nodeIndex) {

        if ('node' !== $elementName) {
            return true;
        }

        $xml = simplexml_load_string($xmlString);
        if($this->hasId((string)$xml->attributes()['id'])) {

            $node = $this->newElement();
            $node->meta = $this->getMeta($xml);
            $node->tag = $this->getTags($xml);

            $this->addNode($node);
        }

        return true;
    }

    protected function hasId($id) {

        if(empty($this->ids)) {
            $ids = [];
            $items = $this->getWays();
            foreach($items as $item) {
                foreach($item->refs as $id) {
                    $ids[$id][$item->meta['id']] = $item->meta['id'];
                }
            }
            $this->ids = $ids;
        }

        return isset($this->ids[$id]) ? $this->ids[$id] : null;
    }

}