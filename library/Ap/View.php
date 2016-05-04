<?php
class Ap_View extends Zend_View
{
    public $formatter;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->formatter = new Ap_View_Formatter();
    }
    
    public function Genres()
    {
        return $this->formatter->Genres();
    }
    
    public function Authors()
    {
        return $this->formatter->Authors();
    }
    
    public function Trends()
    {
        return $this->formatter->Trends();
    }
    
    
    public function AuthorUrl($author)
    {
        return $this->formatter->AuthorUrl($author);
    }

    public function AwayUrl($url)
    {
        return $this->formatter->AwayUrl($url);
    }

    public function BookUrl($book, $short = false)
    {
        return $this->formatter->BookUrl($book, $short);
    }

    public function DownloadBookUrl($book)
    {
        return $this->formatter->DownloadBookUrl($book);
    }
    
    public function DownloadDocumentUrl($doc)
    {
        return $this->formatter->DownloadDocumentUrl($doc);
    }
    
    public function BuyUrl($book)
    {
        return $this->formatter->BuyUrl($book);
    }

    public function CoverUrl($book)
    {
        return $this->formatter->CoverUrl($book);
    }
    
    public function ShowDoc($book)
    {
        return $this->formatter->ShowDoc($book);
    }

    public function ShowDocs($type)
    {
        return $this->formatter->ShowDocs($type);
    }
    
    public function DownloadUrl($book, $idblock = 0, $suffix = "")
    {
        return $this->formatter->DownloadUrl($book, $idblock, $suffix);
    }

    public function LinkUrl($name)
    {
        return $this->formatter->LinkUrl($name);
    }

    public function GenreUrl($genre)
    {
        return $this->formatter->GenreUrl($genre);
    }

    public function ReadUrl($doc)
    {
        return $this->formatter->ReadUrl($doc);
    }

    public function SearchUrl($term, $type = "")
    {
        return $this->formatter->SearchUrl($term, $type);
    }

    public function truncate($string, $start = 0, $length = 100, $prefix = '...', $postfix = '...')
    {
        return $this->formatter->truncate($string, $start, $length, $prefix, $postfix);
    }

    public function truncateName($str, $length = 100)
    {
        return $this->formatter->truncateName($str, $length);
    }

    public function myurl(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        if(count($urlOptions)){
            $part = array();
            foreach($urlOptions AS $var=>$value){
                $part[] = $var.'='.$value;
            }
        $url = '?'.implode('&', $part);
        } else {
            $url = '';
        }
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble(array(), $name, $reset, $encode).$url;
    }
}