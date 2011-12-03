<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_file extends WYSIJA_object{
    
    function WYSIJA_help_file(){
        
    }
    
    /**
     * Get the full path of a file
     * @param type $csvfilename
     * @param type $folder
     * @return boolean 
     */
    function get($csvfilename,$folder="temp"){
        $upload_dir = wp_upload_dir();
        
        $filename=$upload_dir['basedir'].DS."wysija".DS.$folder.DS.$csvfilename;
        if(!file_exists($filename)){
            $filename=$upload_dir['basedir'].DS.$csvfilename;
            if(!file_exists($filename)) $filename=false;
        }
        
        return $filename;
    }
    
    /**
     * create a directory recursively if possible
     * @param type $folder
     * @return string 
     */
    function makeDir($folder="temp"){
        $upload_dir = wp_upload_dir();
        
        $dirname=$upload_dir['basedir'].DS."wysija".DS.$folder.DS;
        if(!file_exists($dirname)){
            if(!@mkdir($dirname, 0755,true)){
                $dirname=false;
                //update_option('');
                update_option('wysija_write_uploads',"not");
                
            }
        }
        
        return $dirname;
    }
    
    
    function getUploadDir($folder=false){
        $upload_dir = wp_upload_dir();
        
        $dirname=$upload_dir['basedir'].DS."wysija".DS;
        if($folder) $dirname.=$folder.DS;
        if(file_exists($dirname))    return $dirname;
        return false;
        /*$foldersUpload=array($dirname,$upload_dir['basedir'].DS);
        foreach($foldersUpload as $folderName){
            if(file_exists($folderName)){
                return $folderName;
            }
        }*/
    }
    
    /**
     * make a temporary file
     * @param type $content
     * @param type $key
     * @param type $format
     * @return type 
     */
    function temp($content,$key="temp",$format=".tmp"){
        $tempDir=$this->makeDir();
        
        if(!$tempDir)   return false;

        
        $filename=$key."-".mktime().$format;
        $handle=fopen($tempDir.$filename, "w");
        fwrite($handle, $content);
        fclose($handle);
        
        return array('path'=>$tempDir.$filename,'name'=>$filename, 'url'=>$this->url($filename,"temp"));
    }
    
    /**
     * Get the url of a wysija file based on the filename and the wysija folder
     * @param type $filename
     * @param type $folder
     * @return string 
     */
    function url($filename,$folder="temp"){
        $upload_dir = wp_upload_dir();

        if(file_exists($upload_dir['basedir'].DS."wysija")){
            $url=$upload_dir['baseurl']."/wysija/".$folder."/".$filename;
        }else{
            $url=$upload_dir['baseurl']."/".$filename;
        }
        return $url;
    }
    
    /**
     * send file to be downloaded
     * @param type $path 
     */
    function send($path){
        /* submit the file to the admin */
        if(file_exists($path)){
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="export_wysija.csv"');
            readfile($path);
            exit();
        }else $this->error(__('File does not exists.',WYSIJA),true);
        
    }
    
    /*
     * 
     */
    function clear(){
        $foldersToclear=array("import","temp");
        $filenameRemoval=array("import-","export-");
        $deleted=array();
        foreach($foldersToclear as $folder){
            $path=$this->getUploadDir($folder);
            /* get a list of files from this folder and clear them */
            
            $files = scandir($path);
            foreach($files as $filename){
                if(!in_array($filename, array('.','..',".DS_Store","Thumbs.db"))){
                    if(preg_match('/('.implode($filenameRemoval,'|').')[0-9]*\.csv/',$filename,$match)){
                       $deleted[]=$path.$filename;
                       
                    }
                }
            }
        }
        foreach($deleted as $filename){
            if(file_exists($filename)){
                unlink($filename);
            }
        }
        
    }
    
}

