<?php
namespace Simplepubgen\Xml;
use Simplepubgen\Simplepubgen;
class Container implements Ressource
{
    /**
     * @var Simplepubgen $book
     */
    private $book = "";
    public function __construct($book, $chapters)
    {
        $this->book = $book;
    }


    /**
     * @return string
     */
    public function getRessourceId():string
    {
        return $this->getId() ;
    }
    /**
     * @return string
     */
    public function getProperties():string
    {
        return "";
    }


    public function getMediaType():string
    {
        return "application/xhtml+xml";
    }

    /**
     * @return string
     */
    public function getId():string
    {
        return "container";
    }

    public function getFileName():string
    {
        return "container.xml";
    }

    public function getRessourceContent():string
    {
        return $this->getContent();
    }
    public function getContent():string
    {
        return $this->book->getAsset($this->book::ASSET_CONTAINER);
    }
}