<?php

namespace muka\OsmParser\Event;

use Symfony\Component\EventDispatcher\Event;

class OsmParserItemEvent extends Event {

    private $currentItem;
    private $currentType;
    private $osmParser;

    public function __construct($currentType, $currentItem, \muka\OsmParser\OsmParser $osmParser)
    {
        $this->currentType = $currentType;
        $this->currentItem = $currentItem;
        $this->osmParser = $osmParser;
    }

    public function getItem()
    {
        return $this->currentItem;
    }

    public function getType()
    {
        return $this->currentType;
    }

    public function getParser()
    {
        return $this->osmParser;
    }

}
