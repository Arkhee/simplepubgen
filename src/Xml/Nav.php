<?php
namespace Simplepubgen\Xml;
use Simplepubgen\Simplepubgen;
class Nav implements Ressource
{
    /**
     * @var Chapter[] $chapters
     */
    private $chapters = array();
    /**
     * @var Simplepubgen $book
     */
    private $book = null;
    public function __construct($book, array $chapters)
    {
        $this->chapters = $chapters;
        $this->book = $book;
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
        //return "nav";
        return "";
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
        return "nav";
    }

    public function getFileName():string
    {
        return "";
    }

    public function getRessourceContent():string
    {
        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent():string
    {
        /*
         *
         * <?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="en-US">
	<head>
		<meta charset="utf-8"/>
		<meta name="generator" content="pandoc"/>
		<title>The Ickabog</title>
		<link rel="stylesheet" type="text/css" href="styles/stylesheet1.css"/>
	</head>
	<body>
		<nav epub:type="toc" id="toc">
			<h1 id="toc-title">The Ickabog</h1>
			<ol class="toc">
				<li id="toc-li-1">
					<a href="text/cover.xhtml">The Ickabog</a>
				</li>
			</ol>
		</nav>
	</body>
</html>

         *
         */
        // Creates an instance of the DOMImplementation class
        $imp = new \DOMImplementation;
        $imp->preserveWhiteSpace = false;
        $imp->formatOutput = true;
        // Creates a DOMDocumentType instance
        $dtd = $imp->createDocumentType('html', '', '');
        $doc = $imp->createDocument("", "", $dtd);

        // Créer l'élément html avec les namespaces nécessaires
        $html = $doc->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
        $html->setAttribute('xml:lang', $this->book->getLang() );

        // Ajouter l'élément html à la document
        $doc->appendChild($html);

        // Créer l'élément head et ses enfants
        $head = $doc->createElement('head');
        $metaCharset = $doc->createElement('meta');
        $metaCharset->setAttribute('charset', 'utf-8');
        $metaGenerator = $doc->createElement('meta');
        $metaGenerator->setAttribute('name', 'generator');
        $metaGenerator->setAttribute('content', 'pandoc');
        $title = $doc->createElement('title', $this->book->getBookTitle());
        $link = $doc->createElement('link');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('type', 'text/css');
        $link->setAttribute('href', '../styles/stylesheet.css');

        // Ajouter les éléments au head
        $head->appendChild($metaCharset);
        $head->appendChild($metaGenerator);
        $head->appendChild($title);
        $head->appendChild($link);

        // Ajouter l'élément head à l'élément html
        $html->appendChild($head);

        // Créer l'élément body et ses enfants
        $body = $doc->createElement('body');
        $nav = $doc->createElement('nav');
        $nav->setAttribute('epub:type', 'toc');
        $nav->setAttribute('id', 'toc');
        $h1 = $doc->createElement('h1', $this->book->getBookTitle());
        $h1->setAttribute('id', 'toc-title');
        $ol = $doc->createElement('ol');
        $ol->setAttribute('class', 'toc');
        foreach($this->chapters as $chapter)
        {
            $li = $doc->createElement('li');
            $li->setAttribute('id', $chapter->getId());
            $a = $doc->createElement('a', $chapter->getTitle());
            $a->setAttribute('href', 'text/'.$chapter->getId().'.xhtml');

            // Ajouter les éléments au li
            $li->appendChild($a);

            // Ajouter les éléments à l'ol
            $ol->appendChild($li);
        }

        // Ajouter les éléments à la nav
        $nav->appendChild($h1);
        $nav->appendChild($ol);

        // Ajouter l'élément nav au body
        $body->appendChild($nav);

        // Ajouter l'élément body à l'élément html
        $html->appendChild($body);

        // Sauvegarder le XML dans un fichier ou afficher
        return $doc->saveXML();
    }

}