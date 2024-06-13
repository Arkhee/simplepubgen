<?php
namespace Simplepubgen\Xml;
use Simplepubgen\Simplepubgen;
class Stylesheet
{
    /**
     * @var Simplepubgen $book
     */
    private $book;
    public function __construct($book, $chapters)
    {
        $this->book = $book;
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
        return "text/css";
    }


    /**
     * @return string
     */
    public function getRessourceId():string
    {
        return $this->getId() ;
    }

    public function getId()
    {
        return $this->book::ASSET_STYLESHEET;
    }
    public function getContent()
    {
        return $this->book->getAsset($this->book::ASSET_STYLESHEET);
    }
}