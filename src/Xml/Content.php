<?php
namespace Simplepubgen\Xml;
use Simplepubgen\Simplepubgen;

class Content implements Ressource
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
        return "application/xhtml+xml";
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
        return $this->book::ASSET_CONTENT;
    }

    public function getFileName():string
    {
        return "content.opf";
    }

    public function getRessourceContent():string
    {
        return $this->getContent();
    }

    public function getContent():string
    {
        /*
<?xml version="1.0" encoding="UTF-8"?>
<package version="2.0" unique-identifier="epub-id-1" xmlns="http://www.idpf.org/2007/opf">
  <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
    <dc:title id="epub-title-1">The Ickabog</dc:title>
    <dc:identifier id="epub-id-1">urn:uuid:dae2fc2d-2f1a-4df0-91f6-b8ad3e108296</dc:identifier>
    <dc:date id="epub-date">2023-06-07T07:00:00+00:00</dc:date>
    <dc:language>en</dc:language>
    <dc:creator opf:role="aut">JK Rowling</dc:creator>
    <meta refines="#epub-creator-1" property="role" scheme="marc:relators">aut</meta>
    <meta name="cover" content="cover_jpg" />
    <meta property="dcterms:modified">2020-06-07T22:40:17Z</meta>
  </metadata>
  <manifest>
	<item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml" />
    <item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav" />
    <item id="stylesheet1" href="styles/stylesheet.css" media-type="text/css" />
    <item id="cover_xhtml" href="text/cover.xhtml" media-type="application/xhtml+xml" properties="svg" />
    <item id="ch000_xhtml" href="text/ch000.xhtml" media-type="application/xhtml+xml" />
    <item id="ch001_xhtml" href="text/ch001.xhtml" media-type="application/xhtml+xml" />
    <item properties="cover-image" id="cover_jpg" href="media/cover.jpg" media-type="image/jpeg" />
  </manifest>
  <spine toc="ncx">
    <itemref idref="cover_xhtml" />
	<itemref idref="ch001_xhtml" linear="yes"  />
  </spine>
  <guide>
    <reference type="toc" title="The Ickabog" href="nav.xhtml" />
    <reference type="cover" title="Cover" href="text/cover.xhtml" />
  </guide>
</package>
        */
        // Créer une nouvelle instance de DOMDocument
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        // Créer l'élément racine package avec les attributs nécessaires
        $package = $doc->createElement('package');
        $package->setAttribute('version', '2.0');
        $package->setAttribute('unique-identifier', $this->book->getCode());
        $package->setAttribute('xmlns', 'http://www.idpf.org/2007/opf');

        // Ajouter l'élément package à la document
        $doc->appendChild($package);

        // Créer l'élément metadata avec les namespaces nécessaires
        $metadata = $doc->createElement('metadata');
        $metadata->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $metadata->setAttribute('xmlns:opf', 'http://www.idpf.org/2007/opf');

        // Créer les éléments enfants du metadata
        $title = $doc->createElement('dc:title', $this->book->getBookTitle());
        //$title->setAttribute('id', $this->book->getCode());

        $identifier = $doc->createElement('dc:identifier', /* 'urn:uuid:'. */ $this->book->getId());
        $identifier->setAttribute('id', $this->book->getCode());

        $date = $doc->createElement('dc:date', date("Y-m-d").'T07:00:00+00:00');
        $date->setAttribute('id', 'epub-date');

        $language = $doc->createElement('dc:language', $this->book->getLang());

        //$creator = $doc->createElement('dc:creator', 'JK Rowling');
        //$creator->setAttribute('opf:role', 'aut');

        /*
        $metaRole = $doc->createElement('meta');
        $metaRole->setAttribute('refines', '#epub-creator-1');
        $metaRole->setAttribute('property', 'role');
        $metaRole->setAttribute('scheme', 'marc:relators');
        $metaRole->appendChild($doc->createTextNode('aut'));
        */
        $metaCover = $doc->createElement('meta');
        $metaCover->setAttribute('name', 'cover');
        $metaCover->setAttribute('content', $this->book->getCover()->getId());

        //$metaModified = $doc->createElement('meta', date("Y-m-d").'T'.date("H:i:s").'Z');
        //$metaModified->setAttribute('property', 'dcterms:modified');

        // Ajouter les éléments au metadata
        $metadata->appendChild($title);
        $metadata->appendChild($identifier);
        $metadata->appendChild($date);
        $metadata->appendChild($language);
        //$metadata->appendChild($creator);
        //$metadata->appendChild($metaRole);
        $metadata->appendChild($metaCover);
        //$metadata->appendChild($metaModified);

        // Ajouter l'élément metadata à l'élément package
        $package->appendChild($metadata);

        // Créer l'élément manifest et ses enfants
        $manifest = $doc->createElement('manifest');

        $ressources = $this->book->getRessources();
        foreach($ressources as $id => $ressource)
        {
            if(!isset($ressource["manifest"]) || empty($ressource["manifest"]))
            {
                continue;
            }
            $item = $doc->createElement('item');
            $item->setAttribute('id', $ressource["id"]);
            $item->setAttribute('href', $ressource["manifest"]);
            $item->setAttribute('media-type', $ressource["media-type"]);
            if(isset($ressource["properties"]) && !empty($ressource["properties"]))
            {
                $item->setAttribute('properties', $ressource["properties"]);
            }
            $manifest->appendChild($item);
        }

        // Ajouter l'élément manifest à l'élément package
        $package->appendChild($manifest);

        // Créer l'élément spine et ses enfants
        $spine = $doc->createElement('spine');
        $spine->setAttribute('toc', 'ncx');

        foreach($ressources as $id => $ressource)
        {
            if($ressource["spine"])
            {
                $itemrefCh001 = $doc->createElement('itemref');
                $itemrefCh001->setAttribute('idref', $ressource["id"]);
                $spine->appendChild($itemrefCh001);
            }
        }

        // Ajouter l'élément spine à l'élément package
        $package->appendChild($spine);

        // Créer l'élément guide et ses enfants
        $guide = $doc->createElement('guide');

        $referenceToc = $doc->createElement('reference');
        $referenceToc->setAttribute('type', 'toc')  ;
        $referenceToc->setAttribute('title', "Table des matières");
        $referenceToc->setAttribute('href', 'nav.xhtml');

        $referenceCover = $doc->createElement('reference');
        $referenceCover->setAttribute('type', 'cover');
        $referenceCover->setAttribute('title', 'Cover');
        $referenceCover->setAttribute('href', 'text/cover.xhtml');

        // Ajouter les éléments au guide
        $guide->appendChild($referenceToc);
        $guide->appendChild($referenceCover);

        // Ajouter l'élément guide à l'élément package
        $package->appendChild($guide);

        // Sauvegarder le XML dans un fichier ou afficher
        return $doc->saveXML();
    }
}