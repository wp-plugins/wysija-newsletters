<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_dividers extends WYSIJA_object {
    function WYSIJA_help_dividers() {
    }
    
    function getAll() {
        $fileHelper =& WYSIJA::get('file', 'helper');
        $dirHandle = $fileHelper->exists('dividers');
        if($dirHandle['result'] === FALSE) {
            return array();
        } else {
            $dividers = array();
            $files = scandir($dirHandle['file']);
            foreach($files as $filename) {

                if(in_array($filename, array('.', '..', '.DS_Store', 'Thumbs.db')) === FALSE) {

                    $dimensions = @getimagesize($dirHandle['file'].DS.$filename);
                    if($dimensions !== FALSE) {
                        list($width, $height) = $dimensions;
                    } else {
                        $width = 564;
                        $height = 1;
                    }
                    $dividers[] = array(
                        'src' => $fileHelper->url($filename, 'dividers'),
                        'width' => $width,
                        'height' => $height
                    );
                }
            }
            return $dividers;
        }
    }
    
    function getDefault() {
        $fileHelper =& WYSIJA::get('file', 'helper');
        return array(
            'src' => $fileHelper->url('solid.jpg', 'dividers'),
            'width' => 564,
            'height' => 1
        );
    }
}