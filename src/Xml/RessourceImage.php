<?php
namespace Simplepubgen\Xml;

use Simplepubgen\Simplepubgen;
use Simplepubgen\Xml\CoverImage;
use Simplepubgen\Tools;

class ResourceImage extends CoverImage
{
    public function __construct($book, $chapters)
    {
        parent::__construct($book, $chapters);
        $this->id = uniqid("resource_");
    }
}