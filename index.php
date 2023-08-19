<?php
        
interface FileIterator {
    public function setObj($obj);
    public function setTags(...$tags);
    public function run();
}
        
class DelTags implements FileIterator {
    private $file;
    private $tags;
    private $num_tags;
    public function setObj($obj) {
        $this -> file = $obj;
    }
    private function warn() {
        echo "Can not open the file!";
    }
    public function setTags (...$tags) {
        $this -> tags = $tags;
        $this -> num_tags = count($tags);
    }
    public function run() {
        $flag = false;
        $counter = 0;
        if (!file_exists($this -> file)) {
            $this -> warn();
            return;
        } 
        $stream = fopen($this -> file, "r");  
        // as we have no big file just save it to array
        $array = file($this -> file);
        fclose($stream);
        $temp_stream = fopen("temp.txt", "w+");
        foreach ($array as $str) {
            // we scan block <head></head>, since meta-tags are only there
            if ($str == "</head>")
                $flag = true;
            if ($flag)
                // copy data line by line to temp file
                file_put_contents("temp.txt", $str, FILE_APPEND);
            else {
                foreach ($this -> tags as $t) {
                    if (mb_strpos($str, $t))
                        $counter ++;    
                }
                if ($counter !== $this -> num_tags) 
                    file_put_contents("temp.txt", $str, FILE_APPEND);
                $counter = 0;
            }
        }
        fclose($temp_stream); 
        // delete original file
        unlink($this -> file);
        // rename temp file like original file
        rename('temp.txt', $this -> file);
    }   
}

$del_meta = new DelTags();
$del_meta -> setObj('data.txt');
$del_meta -> setTags("meta name", "description");
$del_meta -> run();
$del_meta -> setTags("meta name", "keywords");
$del_meta -> run();