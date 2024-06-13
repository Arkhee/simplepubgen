<?php
namespace Arkhee\Simplepubgen\Xml;

class Chapter implements Ressource
{
    private $title = "" ;
    private $content = "" ;
    private $id = "" ;
    private $book = null ;
    public function __construct($book,$title,$content)
    {
        $this->book = $book ;
        $this->title = $title ;
        $this->content = $content ;
        $this->id = uniqid("chap_") ;
    }

    /**
     * @return string
     */
    public function getTitle():string
    {
        return $this->title ;
    }

    /**
     * @return string
     */
    public function getId():string
    {
        return $this->id ;
    }

    public function getMediaType():string
    {
        return "application/xhtml+xml";
    }
    /**
     * @return string
     */
    public function getProperties():string
    {
        return "";
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
    public function getFileName():string
    {
        return $this->getId().".xhtml";
    }

    /**
     * @return string
     * @throws \DOMException
     */
    public function getRessourceContent():string
    {
        return $this->getContent();
    }

    /**
     * @return string
     * @throws \DOMException
     */
    public function getContent():string
    {
        /*
         * Generate an xmlstring for the chapter using this template :
            <?xml version="1.0" encoding="UTF-8"?>
            <!DOCTYPE html>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="en-US">
            <head>
              <meta charset="utf-8" />
              <meta name="generator" content="pandoc" />
              <title>Title</title>
              <link rel="stylesheet" type="text/css" href="../styles/stylesheet1.css" />
            </head>
            <body epub:type="bodymatter">
            <section id="the-ickabog" class="level2" data-number="0.2">
                <h2>Title</h2>
                <div class="entry-content">Content</div>
            </section>
            </body>
            </html>
         */
        // Créer une nouvelle instance de DOMDocument

        // Creates an instance of the DOMImplementation class
        $imp = new \DOMImplementation;

        // Creates a DOMDocumentType instance
        $dtd = $imp->createDocumentType('html', '', '');

        $doc = $imp->createDocument("", "", $dtd);

//        $doc = new \DOMDocument('1.0', 'UTF-8');

        // Créer l'élément DOCTYPE
//        $doctype = $doc->createDocumentType('html', '', '');

        // Append le doctype à la document
//        $doc->appendChild($doctype);

        // Créer l'élément html avec les namespaces nécessaires
        $html = $doc->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
        $html->setAttribute('xml:lang', $this->book->lang);

        // Ajouter l'élément html à la document
        $doc->appendChild($html);

        // Créer l'élément head et ses enfants
        $head = $doc->createElement('head');
        $metaCharset = $doc->createElement('meta');
        $metaCharset->setAttribute('charset', 'utf-8');
        $metaGenerator = $doc->createElement('meta');
        $metaGenerator->setAttribute('name', 'generator');
        $metaGenerator->setAttribute('content', 'pandoc');
        $title = $doc->createElement('title', $this->title);
        $link = $doc->createElement('link');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('type', 'text/css');
        $link->setAttribute('href', '../'.$this->book->getCssRelativePath());

        // Ajouter les éléments au head
        $head->appendChild($metaCharset);
        $head->appendChild($metaGenerator);
        $head->appendChild($title);
        $head->appendChild($link);

        // Ajouter l'élément head à l'élément html
        $html->appendChild($head);

        // Créer l'élément body et ses enfants
        $body = $doc->createElement('body');
        $body->setAttribute('epub:type', 'bodymatter');
        $section = $doc->createElement('section');
        $section->setAttribute('id', $this->id);
        $section->setAttribute('class', 'level2');
        $h2 = $doc->createElement('h2', $this->title);
        $div = $doc->createElement('div', $this->content);
        $div->setAttribute('class', 'entry-content');

        // Ajouter les éléments au body
        $section->appendChild($h2);
        $section->appendChild($div);
        $body->appendChild($section);

        // Ajouter l'élément body à l'élément html
        $html->appendChild($body);

        // Sauvegarder le XML dans un fichier ou afficher
        return $doc->saveXML();
    }
}