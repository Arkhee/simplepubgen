<?php
namespace Simplepubgen\Xml;
use Simplepubgen\Simplepubgen;
class Mimetype implements Ressource
{
    public function __construct($book, $chapters)
    {
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
        return "mimetype";
    }

    public function getFileName():string
    {
        return "mimetype";
    }

    public function getRessourceContent():string
    {
        return $this->getContent();
    }
    public function getContent():string
    {
        return "application/epub+zip";
    }
}