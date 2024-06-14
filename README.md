# SimplePubGen : Simple EPub Generator for PHP websites
Create a simple texte based epub.
This generator has been developped to be used as a library for a project
aimed at producing simple epub based on websites, especially web novels.
Basically, it can be used to generate an epub from a list of chapters that 
would have been collected (manually, automatically) from a website.

## How it works
This software works completely on a programated way.
It is a simple class that can be used in any PHP project.
* First instantiate the class with the following title of the book as a parameter
* Add a cover (url of the cover, the library will download it for you)
* Then add chapters to the book, with the title and the content of the chapter
* If there are more resources to add (images, css, etc), add them to the book with the addResource method
* Finally, call the `generate` method to create the epub file

# How the addResource works
The addResource method is used to add additional files to the epub that are called
from the content of the chapters. This is useful for adding images, css, etc.
You must first adapt the html of the content in order to modify the links.
One image would initially look this way :
```html
<img src="https://mysite/my-old-file.jpeg" />
```
You would have to changer it to this :
```html
<img src="../image/new-file-name.jpeg" />
```
Then you can add the image to the epub with the following code :
```php
$epub->addResource("new-file-name.jpeg","https://mysite/my-old-file.jpeg");
```
As you guessed the file will be stored in an "image" folder inside the ePub
which is located in a different folder from the place contents are stored, 
so the "../image/" is important.

If you use the "WebBookScraper" class to collect the chapters, inline images will 
be automatically added to the epub and the content modified.

## How to install
The project is available on packagist and can be installed using composer:
```bash
composer require "arkhee/simplepubgen"
```

## Example
There is a sample provided to see how it works, have a look at the sample folder
To use it as-is you must use the Simplepubgen and the WebBookScraper packages.
Created a new folder on your server and copy the sample file at it's root
Install both packages with composer and run the sample file.

```bash
composer require "arkhee/simplepubgen"
composer require "arkhee/webbookscraper"
```
