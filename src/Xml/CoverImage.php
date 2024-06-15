<?php

namespace Simplepubgen\Xml;

use Simplepubgen\Simplepubgen;
use Simplepubgen\Tools;

class CoverImage implements Resource
{
    private $imageFile = "";
    private $imageFileName = "";
    protected $id = "";
    private $lang = "";
    /**
     * @var Simplepubgen $book
     */
    private $book = "";

    public function __construct($book, $chapters)
    {
        $this->book = $book;
        //$this->imageFile = $imageFile;
        $this->id = uniqid("cover_");
    }

    public function setCoverImageFile($imageFile)
    {
        $this->imageFile = $imageFile;
    }


    /**
     * @return string
     */
    public function getResourceId(): string
    {
        return $this->id;
    }


    public function getId($file = ""): string
    {
        return "cover";
    }

    /**
     * @return string
     */
    public function getProperties(): string
    {
        return "";
    }


    public function getMediaType($file = ""): string
    {
        return (empty($file) ? "application/xhtml+xml" : Tools::DL_Content_type($file));
    }

    public function getResourceContent(): string
    {
        return file_get_contents($this->imageFile);
    }

    /**
     * @return string
     * Returns the filename of the cover image to be used in the epub
     */
    public function getFileName(): string
    {
        if (empty($this->imageFileName)) {
            $infoPath = pathinfo($this->imageFile);
            $extension = $infoPath['extension'] ?? "";
            $this->imageFileName = $this->id . "." . $extension;
        }
        return $this->imageFileName;
    }

    public function setFileName($name)
    {
        $this->imageFileName = $name;
    }

    public function getContent(): string
    {
        /*
         * <?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="en-US">
<head>
  <meta charset="utf-8" />
  <meta name="generator" content="pandoc" />
  <title>TITRE LIVRE</title>
  <link rel="stylesheet" type="text/css" href="../styles/stylesheet1.css" />
</head>
<body id="cover">
<div id="cover-image">
<h1 class="unnumbered" data-number="">TITRE LIVRE</h1>
<p class="cover-image">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="100%" height="100%" viewBox="0 0 1500 2250" preserveAspectRatio="none">
<image width="1500" height="2250" xlink:href="../media/cover.jpg" />
</svg>
</p>
</div>
</body>
</html>

         */
        // Creates an instance of the DOMImplementation class
        $imp = new \DOMImplementation;

        // Creates a DOMDocumentType instance
        //$dtd = $imp->createDocumentType('html', '', '');
        $dtd = $imp->createDocumentType(
            'html', // Nom qualifié du document type
            '-//W3C//DTD XHTML 1.1//EN', // Public identifier
            'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd' // System identifier
        );

        $doc = $imp->createDocument("", "", $dtd);
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        // Créer une nouvelle instance de DOMDocument
        //$dom = new \DOMDocument('1.0', 'UTF-8');

        // Créer l'élément DOCTYPE
        //$doctype = $doc->createDocumentType('html', '', '');

        // Append le doctype à la document
        //$doc->appendChild($doctype);
        // <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        //  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
        // Créer l'élément html avec les namespaces nécessaires
        $html = $doc->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
        $html->setAttribute('xml:lang', $this->book->getLang());

        // Ajouter l'élément html à la document
        $doc->appendChild($html);

        // Créer l'élément head et ses enfants
        $head = $doc->createElement('head');
        //$metaCharset = $doc->createElement('meta');
        //$metaCharset->setAttribute('charset', 'utf-8');
        $metaGenerator = $doc->createElement('meta');
        $metaGenerator->setAttribute('name', 'generator');
        $metaGenerator->setAttribute('content', 'pandoc');
        $title = $doc->createElement('title', $this->book->getBookTitle());
        $link = $doc->createElement('link');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('type', 'text/css');
        $link->setAttribute('href', '../' . $this->book->getCssRelativePath());

        // Ajouter les éléments au head
        //$head->appendChild($metaCharset);
        $head->appendChild($metaGenerator);
        $head->appendChild($title);
        $head->appendChild($link);

        // Ajouter l'élément head à l'élément html
        $html->appendChild($head);

        // Créer l'élément body et ses enfants
        $body = $doc->createElement('body');
        $body->setAttribute('id', 'cover');
        $divCoverImage = $doc->createElement('div');
        $divCoverImage->setAttribute('id', $this->id);
        $h1 = $doc->createElement('h1', $this->book->getBookTitle());
        $h1->setAttribute('class', 'unnumbered');
        //$h1->setAttribute('data-number', '');
        $pCoverImage = $doc->createElement('p');
        $pCoverImage->setAttribute('class', 'cover-image');

        // Créer l'élément svg et ses enfants
        $svg = $doc->createElement('svg');
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $svg->setAttribute('version', '1.1');
        $svg->setAttribute('width', '100%');
        $svg->setAttribute('height', '100%');
        $svg->setAttribute('viewBox', '0 0 1500 2250');
        $svg->setAttribute('preserveAspectRatio', 'none');
        $image = $doc->createElement('image');
        $image->setAttribute('width', '1500');
        $image->setAttribute('height', '2250');
        $image->setAttribute('xlink:href', '../image/' . $this->getFileName());

        // Ajouter l'élément image à l'élément svg
        $svg->appendChild($image);

        // Ajouter les éléments au paragraphe
        $pCoverImage->appendChild($svg);

        // Ajouter les éléments au div
        $divCoverImage->appendChild($h1);
        $divCoverImage->appendChild($pCoverImage);

        // Ajouter l'élément div au body
        $body->appendChild($divCoverImage);

        // Ajouter l'élément body à l'élément html
        $html->appendChild($body);

        // Sauvegarder le XML dans un fichier ou afficher
        return $doc->saveXML();
    }
}
