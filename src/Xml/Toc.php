<?php
namespace Arkhee\Simplepubgen\Xml;
use Arkhee\Simplepubgen\Simplepubgen;
use Arkhee\Simplepubgen\Xml\Chapter;
class Toc implements Ressource
{
    /**
     * @var Chapter[] $chapters
     */
    private $chapters = array();
    /**
     * @var Simplepubgen $book
     */
    private $book = "";
    public function __construct(string $book, array $chapters)
    {
        $this->chapters = $chapters;
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
        return "application/x-dtbncx+xml";
    }

    
    
    /**
     * @return string
     */
    public function getRessourceId():string
    {
        return $this->getId() ;
    }

    public function getId():string
    {
        return "toc";
    }

    public function getFileName(): string
    {
        return "";
    }

    public function getRessourceContent(): string
    {
        return $this->getContent();
    }


    /**
     * @return string
     * @throws \DOMException
     */
    public function getContent():string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');

        // Créer l'élément racine ncx avec les attributs nécessaires
        $ncx = $doc->createElement('ncx');
        $ncx->setAttribute('version', '2005-1');
        $ncx->setAttribute('xmlns', 'http://www.daisy.org/z3986/2005/ncx/');

        // Ajouter l'élément ncx à la document
        $doc->appendChild($ncx);

        // Créer l'élément head et ses enfants
        $head = $doc->createElement('head');
        $metaUid = $doc->createElement('meta');
        $metaUid->setAttribute('name', 'dtb:uid');
        $metaUid->setAttribute('content', 'urn:uuid:'.$this->book->getId());
        $metaDepth = $doc->createElement('meta');
        $metaDepth->setAttribute('name', 'dtb:depth');
        $metaDepth->setAttribute('content', '1');
        $metaTotalPageCount = $doc->createElement('meta');
        $metaTotalPageCount->setAttribute('name', 'dtb:totalPageCount');
        $metaTotalPageCount->setAttribute('content', '0');
        $metaMaxPageNumber = $doc->createElement('meta');
        $metaMaxPageNumber->setAttribute('name', 'dtb:maxPageNumber');
        $metaMaxPageNumber->setAttribute('content', '0');
        $metaCover = $doc->createElement('meta');
        $metaCover->setAttribute('name', 'cover');
        $metaCover->setAttribute('content', $this->book->getCover()->getId());

        // Ajouter les éléments meta au head
        $head->appendChild($metaUid);
        $head->appendChild($metaDepth);
        $head->appendChild($metaTotalPageCount);
        $head->appendChild($metaMaxPageNumber);
        $head->appendChild($metaCover);

        // Ajouter l'élément head à l'élément ncx
        $ncx->appendChild($head);

        // Créer l'élément docTitle et son enfant
        $docTitle = $doc->createElement('docTitle');
        $textTitle = $doc->createElement('text', $this->book->getBookTitle());
        $docTitle->appendChild($textTitle);

        // Ajouter l'élément docTitle à l'élément ncx
        $ncx->appendChild($docTitle);

        // Créer l'élément navMap et ses enfants
        $navMap = $doc->createElement('navMap');
        foreach($this->chapters as $chapter)
        {
            $navPoint = $doc->createElement('navPoint');
            $navPoint->setAttribute('id', $chapter->getId());
            $navLabel = $doc->createElement('navLabel');
            $textLabel = $doc->createElement('text', $chapter->getTitle());
            $content = $doc->createElement('content');
            $content->setAttribute('src', 'text/'.$chapter->getId().'.xhtml');

            // Ajouter les éléments au navPoint
            $navLabel->appendChild($textLabel);
            $navPoint->appendChild($navLabel);
            $navPoint->appendChild($content);

            // Ajouter l'élément navPoint au navMap
            $navMap->appendChild($navPoint);
        }

        // Ajouter l'élément navMap à l'élément ncx
        $ncx->appendChild($navMap);

        // Sauvegarder le XML dans un fichier ou afficher
        return $doc->saveXML();
    }

}