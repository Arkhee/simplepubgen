<?php
namespace Simplepubgen;
class Tools
{

    /**
     * @param string $text
     * @return string
     * Returns text without weird accents or characters
     */
    public static function CleanHTML(string $html):string
    {
        $html = str_replace("“", '&ldquo;', $html);
        $html = str_replace("”", '&rdquo;', $html);
        $html = str_replace("’", '&rsquo;', $html);
        $html = str_replace("‘", '&lsquo;', $html);
        $html = str_replace("—", '&mdash;', $html);
        $html = str_replace("…", '&hellip;', $html);
        $html = str_replace("–", '&ndash;', $html);
        $html = str_replace("•", '&bull;', $html);
        return $html;
    }


    /**
     * @param string $text
     * @return string
     * Returns text in a format that can be used as a filename
     */
    public static function Text2Code(string $text):string
    {
        $str = htmlentities($text, ENT_NOQUOTES, "utf-8");
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2});#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);
        $str = str_replace(':', '-', $str);
        $str = str_replace('[', '', $str);
        $str = str_replace(']', '', $str);
        $nompage = str_replace("{","",$str);
        $nompage = str_replace("}","",$nompage);
        //$nompage = eregi_replace("[^a-z0-9_:~\\\-|]","_",$nompage);
        $nompage = preg_replace("/[^a-z0-9_:~\\\-|]/i","_",$nompage);
        $nompage = str_replace("|","-",$nompage);
        //$nompage = str_replace("/","_",$nompage);
        while(strpos($nompage,"__")!==false) $nompage=str_replace("__","_",$nompage);
        if(substr($nompage,-1)=="_") $nompage=substr($nompage,0,-1);
        if(substr($nompage,0,1)=="_") $nompage=substr($nompage,1);
        return(strtolower($nompage));
    }


    /**
     * @param string $pathdir
     * @param string $zipcreated
     * @return bool
     * Zip a directory recursively
     */
    public static function ZipDir(string $pathdir,string $zipcreated):bool
    {
        $creationOk=false;
        if(!class_exists("ZipArchive")) throw new \Exception("La classe ZipArchive n'existe pas");
        // Create new zip class
        $zip = new \ZipArchive;
        if($zip -> open($zipcreated, \ZipArchive::CREATE ) === TRUE) {
            // Store the path into the variable
            Tools::ZipDirRecurseAdd($zip,$pathdir,"consultation");
            $zip ->close();
            $creationOk=true;
        }
        return $creationOk;
    }

    private static function ZipDirRecurseAdd($zip,$pathdir,$reldir="")
    {
        $dir = opendir($pathdir);
        while($file = readdir($dir)) {
            if($file==="." || $file==="..") continue;
            if(is_file($pathdir."/".$file)) {
                try
                {
                    $zip->addFile($pathdir."/".$file, $reldir.(empty($reldir)?"":"/").$file);
                }
                catch(\Exception $ex)
                {
                    $debug = $ex->getMessage();
                }
            }
            if(is_dir($pathdir."/".$file))
            {
                Tools::ZipDirRecurseAdd($zip,$pathdir."/".$file,$reldir.(empty($reldir)?"":"/").$file);
            }
        }
    }

    /*
     * Telechargement / affichage d'un fichier à partir de sa source (et redimensionnement d'image à la volée?)
     */
    public static function DL_DownloadProgressive($file_name, $file_path, $action="download")
    {
        if(file_exists($file_path) && is_readable($file_path))
        {
            Tools::DL_Downloadheaders($file_name,filesize($file_path),$action);
            $fh=fopen($file_path,"rb");
            while(!feof($fh)){
                echo fread($fh,8192);
            }
            fclose($fh);
        }
    }

    public static function DL_Downloadheaders($name, $filesize, $action="download")
    {
        header ("Expires: Mon, 10 Dec 2001 08:00:00 GMT");
        header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS']!='on')
        {
            header ("Cache-Control: no-cache, must-revalidate");
            header ("Pragma: no-cache");
        }
        else
        {
            // for SSL connections you have to replace the two previous lines with
            header ("Cache-Control: must-revalidate, post-check=0,pre-check=0");
            header ("Pragma: public");
        }

        //Image: on affiche directement?
        $contenttype = Tools::DL_Content_type($name);
        if($action!="download" && preg_match("/image/i",$contenttype))
        {
            header("Content-Type: ".$contenttype);
        }
        //Pdf: on affiche avec le nom et la taille
        elseif($action!="download" && preg_match("/pdf/i",$contenttype))
        {
            header("Content-Type: ".$contenttype);
            header("Content-Disposition: inline; filename=\"".basename($name)."\";");
            header("Content-Length: ".$filesize);
        }
        //sinon: téléchargement direct
        else
        {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: ".$contenttype);
            header("Content-Disposition: attachment; filename=\"".basename($name)."\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$filesize);
            //header("Content-Type: application/force-download"); header("Content-Type: application/octet-stream"); header("Content-Type: application/download");
        }
    }

    public static function DL_Content_type($name)
    {
        // Defines the content type based upon the extension of the file
        //echo "Le nom est : ".$name."<br>\n";
        $contenttype = "application/octet-stream";
        $contenttypes = array("html" => "text/html",
            "htm" => "text/html",
            "kml" => "application/vnd.google-earth.kml+xml",
            "txt" => "text/plain",
            "gif" => "image/gif",
            "jpeg" => "image/jpeg",
            "jpg" => "image/jpeg",
            "png" => "image/png",
            "sxw" => "application/vnd.sun.xml.writer",
            "sxg" => "application/vnd.sun.xml.writer.global",
            "sxd" => "application/vnd.sun.xml.draw",
            "sxc" => "application/vnd.sun.xml.calc",
            "sxi" => "application/vnd.sun.xml.impress",
            "xls" => "application/vnd.ms-excel",
            "xlsx" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "pptx" => "application/vnd.ms-powerpoint",
            "doc" => "application/msword",
            "docx" => "application/msword",
            "rtf" => "text/rtf",
            "zip" => "application/zip",
            "mp3" => "audio/mpeg",
            "pdf" => "application/pdf",
            "tgz" => "application/x-gzip",
            "gz"  => "application/x-gzip",
            "vcf" => "text/vcf");
        $path_parts = pathinfo($name);
        $myExtension=strtolower($path_parts["extension"]);
        if(isset($contenttypes[$myExtension]))
            $contenttype=$contenttypes[$myExtension];
        /*
        $name = ereg_replace("e"," ",$name);
        foreach ($contenttypes as $type_ext => $type_name)
        {
          if (preg_match ("/$type_ext$/i", $name)) { $contenttype = $type_name; }
        }
        */
        //echo "Contenu pour l'extension ".$myExtension." du fichier ".$name." : ".$contenttype."<br>\n";
        return $contenttype;
    }


}