<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_view_front_widget_nl extends WYSIJA_view_front {
    
    function WYSIJA_view_front_widget_nl(){
        $this->model=&WYSIJA::get("user","model");
    }
    
    function display($title="",$params,$echo=true){
        $this->addScripts();
        $data=$labelemail="";
        $formidreal="form-".$params['id_form'];
        if(isset($_POST['wysija']['user']['email']) && isset($_POST['formid'])){
            if($formidreal==$_POST['formid'])    $data.= $this->messages();
        }
        $data.= $title;

        $disabledSubmit=$msgsuccesspreview='';
        if(isset($params['preview'])){
            $disabledSubmit='disabled="disabled"';
            $msgsuccesspreview='<div class="allmsgs"><div class="updated">'.$params["success"].'</div></div>';
        }
        
        $data.='<div id="msg-'.$formidreal.'" class="wysija-msg ajax">'.$msgsuccesspreview.'</div>
        <form id="'.$formidreal.'" method="post" action="" class="widget_wysija form-valid-sub">';
            if(isset($params['instruction']))   $data.='<p class="wysija-instruct">'.$params['instruction'].'</p>';
            $submitbutton='<input type="submit" '.$disabledSubmit.' class="wysija-submit wysija-submit-field" name="submit" value="'.esc_attr($params['submit']).'"/>';
            $dataCf=$this->customFields($params,$formidreal,$submitbutton);
            if($dataCf){
                $data.=$dataCf;
            }else{
                $classValidate="wysija-email ".$this->getClassValidate($this->model->columns['email'],true);
                $data.='<p><input type="text" id="'.$formidreal.'-wysija-to" class="'.$classValidate.'" name="wysija[user][email]" />';
                if(!isset($params['preview'])) $data.=$this->honey($params,$formidreal);
                $data.=$submitbutton.'</p>';
            }
            
            
            if(isset($params["lists"])) $listexploded=esc_attr(implode(',',$params["lists"]));
            else $listexploded="";
            
            if(!isset($params['preview'])){
                $data.='<input type="hidden" name="formid" value="'.esc_attr($formidreal).'" />
                    <input type="hidden" name="action" value="save" />
                <input type="hidden" name="wysija[user_list][list_ids]" value="'.$listexploded.'" />
                <input type="hidden" name="message_success" value="'.esc_attr($params["success"]).'" />
                <input type="hidden" name="controller" value="subscribers" />';
                $data.=$this->secure(array('action'=>'save','controller'=>'subscribers'),false,false);
                
                $data.='<input type="hidden" value="1" name="wysija-page" />';
                $data.='<input type="hidden" value="'.wp_create_nonce("wysija_ajax").'" id="wysijax" />';
            }

            
	$data.='</form>';

        if($echo) echo $data;
        else return $data;
    }
    
    function customFields($params,$formidreal,$submitbutton){
        $html="";
        $validationsCF=array(
            'email' => array("req"=>true,"type"=>"email","defaultLabel"=>__("Email",WYSIJA)),
            'firstname' => array("req"=>true,"defaultLabel"=>__("First name",WYSIJA)),
            'lastname' => array("req"=>true,"defaultLabel"=>__("Last name",WYSIJA)),
        );
        if(isset($params['customfields']) && $params['customfields']){
            foreach($params['customfields'] as $fieldKey=> $field){
                $classField='wysija-'.$fieldKey;
                $classValidate=$classField." ".$this->getClassValidate($validationsCF[$fieldKey],true);
                if(!isset($field['label']) || !$field['label']) $field['label']=$validationsCF[$fieldKey]['defaultLabel'];
                if($fieldKey=="email") $fieldid=$formidreal."-wysija-to";
                else $fieldid=$formidreal.'-'.$fieldKey;
                
                if(isset($params['labelswithin'])){
                     if($params['labelswithin']=='labels_within'){
                        $fieldstring='<input type="text" id="'.$fieldid.'" value="'.$field['label'].'" class="defaultlabels '.$classValidate.'" name="wysija[user]['.$fieldKey.']" />';
                    }else{
                        $fieldstring='<label for="'.$fieldid.'">'.$field['label'].'</label><input type="text" id="'.$fieldid.'" class="'.$classValidate.'" name="wysija[user]['.$fieldKey.']" />';
                    }
                }else{
                    $fieldstring='<label for="'.$fieldid.'">'.$field['label'].'</label><input type="text" id="'.$fieldid.'" class="'.$classValidate.'" name="wysija[user]['.$fieldKey.']" />';
                }
               
                $html.='<p class="wysija-p-'.$fieldKey.'">'.$fieldstring.'</p>';
            }
            
            if(!isset($params['preview'])) $html.=$this->honey($params,$formidreal);
            
            if($html) $html.=$submitbutton;
        }
        
        return $html;
    }
    
    function honey($params,$formidreal){
        $arrayhoney=array(
            "firstname"=>array('label'=>__("First name",WYSIJA),"type"=>"req"),
            "lastname"=>array('label'=>__("Last name",WYSIJA),"type"=>"req"),
            "email"=>array('label'=>__("Email",WYSIJA),"type"=>"email")
            
            );
        $html="";
        foreach($arrayhoney as $fieldKey=> $field){
            $fieldid=$formidreal.'-abs-'.$fieldKey;

            if(isset($params['labelswithin'])){
                $fieldstring='<input type="text" id="'.$fieldid.'" value="" class="defaultlabels validated[abs]['.$field['type'].']" name="wysija[user][abs]['.$fieldKey.']" />';
            }else{
                $fieldstring='<label for="'.$fieldid.'">'.$field['label'].'</label><input type="text" id="'.$fieldid.'" class="validated[abs]['.$field['type'].']" name="wysija[user][abs]['.$fieldKey.']" />';
            }
            $html.='<span class="wysija-p-'.$fieldKey.' abs-req">'.$fieldstring.'</span>';
        }
        return $html;
    }
    
}
