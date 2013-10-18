<?php

namespace Osm\Parser;

class RelationParser extends OsmParser {

    public function processNode($xmlString, $elementName, $nodeIndex) {

        if ('relation' !== $elementName) {
            return true;
        }

        $xml = simplexml_load_string($xmlString);
        $relation = $this->newElement();
        $relation->meta = $this->getMeta($xml);
        $relation->members = $this->getMembers($xml);
        $relation->tags = $this->getTags($xml);

        $this->addRelation($relation);

        return true;
    }

    protected function getMembers($xml) {
        $members = [];
        foreach($xml->members as $member) {
            $member = $this->toArray($member);
            $members[$member['ref']] = $member;
        }
        return $members;
    }


}