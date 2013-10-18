<?php

namespace muka\OsmParser;

class WayParser extends OsmParser {

    public function processNode($xmlString, $elementName, $nodeIndex) {

        if (!in_array($elementName, ['bounds', 'way',])) {
            return true;
        }

        $xml = simplexml_load_string($xmlString);

        // do it once
        if($elementName == 'bounds') {
            $bounds = $this->newElement();
            $bounds->meta = $this->toArray($xml);
            $bounds->meta['id'] = 0;
            $this->addElement("bounds", $bounds);
            return true;
        }

        $found = false;
        foreach ($xml->tag as $tag) {
            $key = (string)$tag['k'];
            $val = (string)$tag['v'];
            if($found = $this->findKey($key, $val)) {
                break;
            }
        }

        if(!$found) {
            return true;
        }

        $item = $this->newElement();
        $item->meta = $this->getMeta($xml);
        $item->tags = $this->getTags($xml);
        $item->refs = $this->getNodesRef($xml);

        $this->addWay($item);

        return true;
    }

    function findKey($xmlKey, $xmlVal) {
        if(!$this->keysMap) {
            return true;
        }
        if(isset($this->keysMap[$xmlKey])) {
            if (in_array($xmlVal, $this->keysMap[$xmlKey])) {
                return true;
            }
        }
        return false;
    }

}