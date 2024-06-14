<?php
/*
 * Note : this sample requires the WebBookScraper package to do the extraction work first
 */
require_once("./vendor/autoload.php");
use WebBookScraper\WebBookScraper;
use Simplepubgen\Simplepubgen;
if(isset($_POST["url"]))
{
    $url = $_POST["url"];
    $book = new WebBookScraper($url,true);
    $book->setLogFile(__DIR__.'/log.txt');
    $book->setCacheDir(__DIR__.'/cache');
    $book->useCache(true);
    $book->getBook();
    // First instantiate the class and provide a title
    $epub = new Simplepubgen($book->cover->title);
    // Optional : provide an URL for the cover illustration
    $epub->setCover($book->cover->illustration);
    // Loop over the chapters
    foreach($book->chapters as $chapter)
    {
        // For each chapter provite a title and an HTML content
        // Content MUST be cleaned priori to sending to addChapter
        $epub->addChapter($chapter->title,$chapter->content);
        foreach($chapter->getExternalResources() as $resource)
        {
            // Optionnally : provide additionnal ressources like images located in the webpages
            $epub->addResource($resource->getResourceName(),$resource->getResourceURL());
        }
    }
    // Generate ePub, it will get a name based on the title provided
    $epub->generateEpub() ;
    // Option : provide a name for the file to download right away
    //$epub->generateEpub($epubFileName) ;
    // Option : provide a name for the file to store the epub on the server insetead
    //$epub->generateEpub($epubFileName, $epubFileNameOnServer) ;

}
?>
<html lang="en-EN">
<head>
    <title>WebBookScraper</title>

</head>
<body>
<form method="post">
    <label for="url">Type URL to scrape :</label>
    <input type="text" id="url" name="url" placeholder="Book URL">
    <input type="submit" value="Let's go get the book">
</form>
</body>
</html>
