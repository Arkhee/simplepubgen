<?php
namespace Simplepubgen;
use Simplepubgen\Tools;
use Simplepubgen\Xml\Chapter;
use Simplepubgen\Xml\Cover;
use Simplepubgen\Xml\Toc;
use Simplepubgen\Xml\Nav;
use Simplepubgen\Xml\Mimetype;
use Simplepubgen\Xml\Content;

class Simplepubgen
{
    const ASSET_STYLESHEET = "stylesheet.css";
    const ASSET_CONTENT = "content.xhtml";
    const ASSET_CONTAINER = "container.xml";
    const LOCATION_CONTENT_META = "META-INF/";
    const LOCATION_CONTENT_ROOT = "OEBPS/";
    const LOCATION_CONTENT_TEXT = "text/";
    const LOCATION_CONTENT_CSS = "css/";
    const LOCATION_CONTENT_IMAGE = "image/";

    const STATIC_RESSOURCES_LIST = array(
        array("path" => "", "file"=>"mimetype", "class" => "Mimetype" , "manifest"=> false, "spine"=>false),
        array("path" => self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_TEXT, "file"=>"cover.xhtml", "class" => "Cover", "manifest"=>self::LOCATION_CONTENT_TEXT, "spine"=>true),
        array("path" => self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_CSS, "file"=>self::ASSET_STYLESHEET, "class" => "Stylesheet", "manifest"=>self::LOCATION_CONTENT_CSS, "spine"=>false),
        array("path" => self::LOCATION_CONTENT_ROOT, "file"=>"nav.xhtml", "class" => "Nav", "manifest"=>"", "spine"=>false),
        array("path" => self::LOCATION_CONTENT_ROOT, "file"=>"toc.ncx", "class" => "Toc", "manifest"=>"", "spine"=>false),
        array("path" => self::LOCATION_CONTENT_ROOT, "file"=>"content.opf", "class" => "Content", "manifest"=>"", "spine"=>false),
        array("path" => self::LOCATION_CONTENT_META, "file"=>"container.xml", "class" => "Container", "manifest"=>"", "spine"=>false)
    );
    const DYNAMIC_RESSOURCES_LIST = array(
        array("path" => self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_TEXT, "file"=>"", "data" => "chapters", "class" => "Chapter", "manifest"=>self::LOCATION_CONTENT_TEXT, "spine"=>true),
        array("path" => self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_IMAGE, "file"=>"", "data" => "cover", "class" => "Cover", "manifest"=>self::LOCATION_CONTENT_IMAGE, "spine"=>false)
    );
    /**
     * @var Cover $cover
     */
    private $cover = null;
    private $title="";
    private $id = "";
    private $lang = "";
    private $code = "";
    private $ressources = [];
    private $coverTmpFile = "";
    /**
     * @var Chapter[] $chapters
     */
    private $chapters=array();
    public function __construct($title,$lang="en-US")
    {
        $this->title = $title;
        $this->id = "book_" . md5($title);
        $this->code = Tools::Text2Code($title);
        $this->lang = $lang;
        $this->cover=new Cover($this, $this->chapters);
    }

    public function __destruct()
    {
        if(file_exists($this->coverTmpFile))
        {
            unlink($this->coverTmpFile);
        }
    }

    /**
     * @return array
     */
    public function getRessources():array
    {
        return $this->ressources;
    }

    /**
     * @return string
     */
    public function getCode():string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Cover
     */
    public function getCover():Cover
    {
        return $this->cover;
    }

    /**
     * @param string $imgFile
     * @return bool
     */
    public function setCover(string $imgFile):bool
    {
        if(!file_exists($imgFile) && !empty($imgFile))
        {
            $pathinfo = pathinfo($imgFile);
            if(isset($pathinfo["extension"]))
            {
                $tmpFile = sys_get_temp_dir()."/".uniqid("cover_").".".$pathinfo["extension"];
                file_put_contents($tmpFile,file_get_contents($imgFile));
                $imgFile = $tmpFile;
                $this->coverTmpFile = $imgFile;
            }
        }
        if(file_exists($imgFile) && is_readable($imgFile))
        {
            $this->cover->setCoverImageFile($imgFile);
            return true;
        }
        return false;
    }

    /**
     * @param string $outputFile
     * @param string $destFile
     * @return bool
     */
    public function generateEpub(string $outputFile, string $destFile=""):bool
    {
        $tmpFile = sys_get_temp_dir()."/".uniqid("epub_").".epub";
        $this->createEpub($tmpFile);
        if(!empty($destFile))
        {
            if(file_exists($destFile) && !is_writable($destFile))
            {
                return false;
            }
            if(file_exists($destFile)) unlink($destFile);
            rename($tmpFile, $destFile);
        }
        else
        {
            Tools::DL_DownloadProgressive($outputFile,$tmpFile);
            unlink($tmpFile);
            die();
        }
        return true;
    }

    /**
     * @param string $title
     * @param string $content
     * @return void
     * Add a chapter to the book providing a title and a content
     */
    public function addChapter(string $title,string $content)
    {
        $this->chapters[] = new Chapter($this,$title,$content);
    }


    /**
     * @return string
     */
    public function getLang():string
    {
        return $this->lang;
    }


    /**
     * @return string
     */
    public function getBookTitle():string
    {
        return $this->title;
    }

    public function getAsset($asset)
    {
        return file_get_contents(__DIR__."/assets/".$asset);
    }

    /**
     * @return string
     */
    public function getCssRelativePath():string
    {
        return self::LOCATION_CONTENT_CSS.self::ASSET_STYLESHEET;
    }

    private function prepareRessources()
    {
        foreach(self::STATIC_RESSOURCES_LIST as $ressource)
        {
            $class = $ressource["class"];
            if(empty($class)) continue;
            if(!class_exists($class))
            {
                $class = "Simplepubgen\\Xml\\".$class;
                if(!class_exists($class))
                {
                    continue;
                }
            }
            $object = new $class($this, $this->chapters);
            $this->ressources[$ressource["path"].$ressource["file"]] =
                array(
                    "id" => $object->getId(),
                    "content"=>$object->getContent(),
                    "spine"=>$ressource["spine"],
                    "path"=>$ressource["path"].$ressource["file"],
                    "media-type"=>$object->getMediaType(),
                    "properties"=>$object->getProperties(),
                    "manifest"=>(($ressource["manifest"]!==false)?($ressource["manifest"].$ressource["file"]):null)
                );
        }
        foreach(self::DYNAMIC_RESSOURCES_LIST as $ressource)
        {
            $property = $ressource["data"];
            $curProperty = $this->$property;
            if(!is_array($curProperty))
            {
                $curProperty = array($curProperty);
            }
            foreach($curProperty as $data)
            {
                $this->ressources[$ressource["path"].$data->getFileName()] =
                array(
                    "id" => $data->getRessourceId(),
                    "content"=>$data->getRessourceContent(),
                    "spine"=>$ressource["spine"],
                    "path"=>$ressource["path"].$data->getFileName(),
                    "media-type"=>$data->getMediaType(),
                    "properties"=>$data->getProperties(),
                    "manifest"=>(($ressource["manifest"]!="")?($ressource["manifest"].$data->getFileName()):null)
                );
            }
        }
    }
    private function createEpub($tmpFile)
    {
        $this->prepareRessources();
        $zip = new \ZipArchive;
        if($zip -> open($tmpFile, \ZipArchive::CREATE ) === TRUE) {
            foreach($this->ressources as $path => $content)
            {
                $zip->addFromString($path,$content["content"]);
            }
            /*
            $zip->addFromString("mimetype","application/epub+zip");
            $zip->addFromString(self::LOCATION_CONTENT_META."container.xml",(new Container($this, $this->chapters))->getContent());
            $zip->addFromString(self::LOCATION_CONTENT_ROOT."content.opf",(new Content($this, $this->chapters))->getContent());
            $zip->addFromString(self::LOCATION_CONTENT_ROOT."toc.ncx",(new Toc($this->title, $this->chapters))->getContent());
            $zip->addFromString(self::LOCATION_CONTENT_ROOT."nav.xhtml",(new Nav($this, $this->chapters))->getContent());
            $zip->addFromString(self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_TEXT."cover.xhtml",$this->cover->getContent());
            $zip->addFromString(self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_IMAGE.$this->cover->getFileName(),$this->cover->getRessourceContent());
            $zip->addFromString(self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_CSS.self::ASSET_STYLESHEET,$this->getAsset(self::ASSET_STYLESHEET));
            foreach($this->chapters as $chapter)
            {
                $zip->addFromString(self::LOCATION_CONTENT_ROOT.self::LOCATION_CONTENT_TEXT.$chapter->getFileName(),$chapter->getRessourceContent());
            }
            */
            $zip->close();
        }
    }

}