<?php
defined('WYSIJA') or die('Restricted access');

class WYSIJA_help_licence extends WYSIJA_help{
    function WYSIJA_help_licence(){
        parent::WYSIJA_help();
    }
    function getDomainInfo(){
        $data=array();
        $url=admin_url('admin.php');
        $helperToolbox=&WYSIJA::get("toolbox","helper");
        $data['domain_name']=$helperToolbox->_make_domain_name($url);
        $data['url']=$url;
        $data[uniqid()]=uniqid('WYSIJA');
        $data=base64_encode(serialize($data));
        return $data;
    }
    function check($js=false){
        $data=$this->getDomainInfo();
        if(!$js) {
            WYSIJA::update_option("wysijey",$data);
        }
        $res['domain_name']=$data;
        $res['nocontact']=false;
        $httpHelp=&WYSIJA::get("http","helper");
        $jsonResult = $httpHelp->request('http://www.wysija.com/?wysijap=checkout&wysijashop-page=1&controller=customer&action=checkDomain&data='.$data);
        if($jsonResult){
            


            if($jsonResult){
                $decoded=json_decode($jsonResult);
                if(isset($decoded->msgs))   $this->error($decoded->msgs);
                if($decoded->result){
                    $res['result']=true;

                    $dataconf=array('premium_key'=>base64_encode(get_option('home').mktime()),'premium_val'=>mktime());
                    $this->notice(__("Premium version is valid for your site.",WYSIJA));
                    WYSIJA::update_option("wysicheck",false);
                }else{
                    $dataconf=array('premium_key'=>"",'premium_val'=>"");
                    $this->error(str_replace(array("[link]","[/link]"),array('<a href="http://www.wysija.com/?wysijap=checkout&wysijashop-page=1&controller=orders&action=checkout&wysijadomain='.$data.'" target="_blank">','</a>'),
                        __("Premium version licence does not exists for your site. Purchase from our website [link]here[/link].",WYSIJA)),1);
                }
                $modelConf=&WYSIJA::get("config","model");
                $modelConf->save($dataconf);
            }else{
                $res['nocontact']=true;
                 WYSIJA::update_option("wysicheck",true);

            }
        }else{
            $res['nocontact']=true;
            WYSIJA::update_option("wysicheck",true);
        }
        
        return $res;
    }
}
