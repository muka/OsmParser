<?php

namespace muka\OsmParser\Streamer;

use pwerk\XmlStreamer\XmlStreamer;

abstract class BzipXmlStreamer extends XmlStreamer
{
    protected $isBzip = false;

    public function __construct($mixed, $chunkSize = 16384, $customRootNode = null, $totalBytes = null, $customChildNode = null) {

        if (is_string($mixed)) {

            if(!file_exists($mixed)) {
                throw new \muka\OsmParser\Exception\OsmParserException("File does not exists.");
            }
            if(!is_readable($mixed)) {
                throw new \muka\OsmParser\Exception\OsmParserException("File is not readable.");
            }

            $this->isBzip = (substr($mixed, -3) === 'bz2');

            $fopen = $this->isBzip ? 'bzopen' : 'fopen';

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

//        parent::__construct($this->handle, $this->chunkSize, $this->customRootNode, $this->totalBytes, $this->customChildNode);
    }

    protected function readNextChunk() {

        if($this->isBzip) {

            $this->chunk .= bzread($this->handle, 8096);
            $this->readBytes += $this->chunkSize;


            if(($bzerr = bzerrno($this->handle)) != 0) {
                throw new Exception("Bzip reading error", $bzerr);
            }

            if (feof($this->handle)) {
                $this->readBytes = $this->totalBytes;
                return false;
            }

        }
        else {

            $this->chunk .= fread($this->handle, $this->chunkSize);
            $this->readBytes += $this->chunkSize;

            if ($this->readBytes >= $this->totalBytes) {
                $this->readBytes = $this->totalBytes;
                return false;
            }
        }


        return true;
    }

    protected function closeHandle() {
        $fclose = $this->isBzip ? "bzclose": "fclose";
        $fclose($this->handle);
    }

}