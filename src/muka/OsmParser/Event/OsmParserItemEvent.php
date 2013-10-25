<?php

namespace muka\OsmParser\Event;

use Symfony\Component\EventDispatcher\Event;

class OsmParserItemEvent extends Event {

    private $currentItem;
    private $currentType;

    public function __construct($currentType, $currentItem)
    {
        $this->currentType = $currentType;
        $this->currentItem = $currentItem;
    }

    public function getItem()
    {
        return $this->currentItem;
    }

    public function getType()
    {
        return $this->currentType;
    }

}
