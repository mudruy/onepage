<?php
class Ap_Controller_Action_Special extends Ap_Controller_Action_Cli
{
    CONST URLS_AMOUNT = 50000;
    protected $sitemapPath = "/../public/";
    protected $formatter;
    
    public function init()
    {
        $this->formatter = new Ap_View_Formatter();
         parent::init();
    }

    protected function _generateAuthorsSitemap()
    {
        $sitemapsUrls = array();
        $aut = Am_Core::getTable('Authors');
        $authorsAmount = $aut->getCount();
        $num = ceil($authorsAmount/self::URLS_AMOUNT);
        $protocol = conf('protocol');
        for($i=1; $i<=$num; $i++){
            $authors = $aut->getListAutor(self::URLS_AMOUNT, $i, false);
            $str = '<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
            foreach($authors as $author){
                $url = $this->formatter->AuthorUrl($author);
                $str.= '<url><loc>'.$url.'</loc></url>'.PHP_EOL;
            }
            $str.= '</urlset>';
            $name = "sitemap_autors_$i.gz";
            $sitemapsUrls[] =$this->_saveSitemap($name, $str);
            unset($authors);
            unset($str);
        }
        return $sitemapsUrls;
    }

    protected function _generateBookssSitemap()
    {
        $sitemapsUrls = array();
        $booksTable = Am_Core::getTable('Books');
        $booksAmount = $booksTable->getCount();
        $num = ceil($booksAmount/self::URLS_AMOUNT);
        $protocol = conf('protocol');
        for($i=1; $i<=$num; $i++){
            $str = '<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
            $books = $booksTable->getListBooks(self::URLS_AMOUNT, $i);
            foreach($books as $book){
                $url = $this->formatter->BookUrl($book);
                $str.= '<url><loc>'.$url.'</loc></url>'.PHP_EOL;
            }
            $str.= '</urlset>';
            $name = "sitemap_books_$i.gz";
            $sitemapsUrls[] =$this->_saveSitemap($name, $str);
            unset($books);
            unset($str);
        }
        return $sitemapsUrls;
    }

    protected function _generateGenresSitemap()
    {
        $g = Am_Core::getTable('Genres');
        $el = new Zend_Paginator_Adapter_Array($g->getAllWithoutPopularity(true));
        $genres = $el->getItems(0, self::URLS_AMOUNT);
        $protocol = conf('protocol');
        $str = '<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
        foreach($genres as $genre){
            $url = $this->formatter->GenreUrl($genre);
            $str.= '<url><loc>'.$url.'</loc><changefreq>daily</changefreq></url>'.PHP_EOL;
        }
        $str.= '</urlset>';
        $name = "sitemap_genres.gz";
        $sitemapsUrl =$this->_saveSitemap($name, $str);
        unset ($str);
        return $sitemapsUrl;
    }

    protected function _generatePdfSitemap()
    {
        $sitemapsUrls = array();
        $pdfTable = Am_Core::getTable('PdfSitemap');
        $pdfAmount = $pdfTable->getCount();
        $num = ceil($pdfAmount/self::URLS_AMOUNT);
        $protocol = conf('protocol');
        for($i=1; $i<=$num; $i++){
            $str = '<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
            $documents = $pdfTable->getListPdf(self::URLS_AMOUNT, $i);
            foreach($documents as $pdf){
                $url = $this->formatter->ShowDoc($pdf);
                $str.= '<url><loc>'.$url.'</loc></url>'.PHP_EOL;
            }
            $str.= '</urlset>';
            $name = "sitemap_pdf_$i.gz";
            $sitemapsUrls[] =$this->_saveSitemap($name, $str);
            unset($documents);
            unset($str);
        }
        return $sitemapsUrls;
    }

    protected function _generateDocSitemap()
    {
        return $this->_generateTypeSitemap('Doc');
    }

    protected function _generatePptSitemap()
    {
        return $this->_generateTypeSitemap('Ppt');
    }

    protected function _generateRtfSitemap()
    {
        return $this->_generateTypeSitemap('Rtf');
    }

    protected function _generateTypeSitemap($type)
    {
        $sitemapsUrls = array();
        $table = Am_Core::getTable($type);
        $amount = $table->getCount();
        $type = strtolower($type);
        $num = ceil($amount/self::URLS_AMOUNT);
        $protocol = conf('protocol');
        for($i=1; $i<=$num; $i++){
            $str = '<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
            $documents = $table->getList(self::URLS_AMOUNT, $i);
            foreach($documents as $doc){
                $url = $this->formatter->ShowDoc($doc);
                $str.= '<url><loc>'.$url.'</loc></url>'.PHP_EOL;
            }
            $str.= '</urlset>';
            $name = "sitemap_{$type}_$i.gz";
            $sitemapsUrls[] =$this->_saveSitemap($name, $str);
            unset($documents);
            unset($str);
        }
        return $sitemapsUrls;
    }
    
    private function _saveSitemap($name, $data)
    {
        $gz = gzopen(APPLICATION_PATH.$this->sitemapPath.$name, 'wb3');
        gzwrite($gz, $data);
        gzclose($gz);
        // file_put_contents(APPLICATION_PATH.$this->sitemapPath.$name, $str);
        return conf('protocol').conf ('maindomain')."/".$name;
    }
}