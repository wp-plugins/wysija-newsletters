<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_control_back_tmce extends WYSIJA_control{
    
    function WYSIJA_control_back_tmce(){
        if(!current_user_can('administrator')) die("Action is forbidden.");
        parent::WYSIJA_control();
        $this->viewObj=&WYSIJA::get('tmce','view');
    }

    
    function registerAdd(){
        $this->viewObj->title=__("Insert Newsletter Registration Form",WYSIJA);

        $this->viewObj->registerAdd($this->getData());
        exit;
    }
    
    function registerEdit(){
        $this->viewObj->title=__("Edit Newsletter Registration Form",WYSIJA);

        $this->viewObj->registerAdd($this->getData());
        exit;
    }
    
    
    function getData(){
        $datawidget=array();
        if(isset($_REQUEST['widget-data64'])){
            $datawidget=unserialize(base64_decode($_REQUEST['widget-data64']));
            $datawidget['preview']=true;
        }

        if(isset($_POST['widget-wysija'])){
            $datawidget=array('widget_id'=>$_POST['widget_id'],'preview'=>true);

            foreach($_POST['widget-wysija'] as $arra){
                foreach($arra as $k => $v) {
                    if($k=="lists") {
                        if(isset($datawidget[$k]))  $datawidget[$k][]=$v[0];
                        else    $datawidget[$k]=array($v[0]);
                    }
                    else    $datawidget[$k]=stripslashes($v);
                }
            }
        }
        return $datawidget;
    }

}