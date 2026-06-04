<?php

namespace App\Domain\File;

class FileOriginalInfo
{
    public function __construct(
        public $originalName = null
    ){}

    public function toArray(){
        return [ "originalName" => $this->originalName];
    }
}