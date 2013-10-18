<?php

namespace Osm\Parser;

use pwerk\XmlStreamer\XmlStreamer;

abstract class BzipXmlStreamer extends XmlStreamer
{
    protected $isBzip = false;

    public function __construct($mixed, $chunkSize = 16384, $customRootNode = null, $totalBytes = null, $customChildNode = null) {

        if (is_string($mixed)) {

            $this->isBzip = (substr($mixed, -3) === 'bz2');

            $fopen = $this->isBzip ? 'bzopen' : 'fopen';
            $chunkSize = $this->isBzip ? 8092 : $chunkSize;

            $this->handle = $fopen($mixed, "r");
            if (isset($totalBytes)) {
                $this->totalBytes = $totalBytes;
            } else {
                $this->totalBytes = filesize($mixed);
            }
        } else if (is_resource($mixed)) {
            $this->handle = $mixed;
            if (!isset($totalBytes)) {
                throw new Exception("totalBytes parameter required when supplying a file handle.");
            }
            $this->totalBytes = $totalBytes;
        }

        $this->chunkSize = $chunkSize;
        $this->customRootNode = $customRootNode;
        $this->customChildNode = $customChildNode;

        parent::__construct($this->handle, $this->chunkSize, $this->customRootNode, $this->totalBytes, $this->customChildNode);
    }

    private function readNextChunk() {

        $fread = $this->isBzip ? "bzread": "fread";
        $this->chunk .= $fread($this->handle, $this->chunkSize);
        $this->readBytes += $this->chunkSize;

        if($this->isBzip && ($bzerr = bzerrno($this->handle)) > 0) {
            return $bzerr;
        }

        if ($this->readBytes >= $this->totalBytes) {
            $this->readBytes = $this->totalBytes;
            return false;
        }
        return true;
    }

    private function closeHandle() {
        $fclose = $this->isBzip ? "bzclose": "fclose";
        $fclose($this->handle);
    }

}