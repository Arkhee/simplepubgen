<?php
namespace Arkhee\Simplepubgen\Xml;
interface Ressource
{
    public function getContent():string;
    public function getId():string;
    public function getRessourceId():string;
    public function getFileName():string;
    public function getRessourceContent():string;
    public function getProperties():string;
    public function getMediaType():string;

}