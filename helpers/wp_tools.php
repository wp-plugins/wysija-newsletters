<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_wp_tools extends WYSIJA_object{
    function WYSIJA_help_wp_tools(){
    }

    function set_default_rolecaps(){
        

        $rolesadmin=array('administrator','super_admin');
        foreach($rolesadmin as $roladm){
            $role = get_role($roladm);
            if(!$role) continue;

            $arr=array('wysija_newsletters','wysija_subscribers','wysija_subscriwidget','wysija_config');
            foreach($arr as $arrkey){
                if(!$role->has_cap($arrkey)) $role->add_cap( $arrkey );
            }
        }
    }


    function wp_get_roles() {
        
        global $wp_roles;
        $all_roles = $wp_roles->roles;
        $editable_roles = apply_filters('editable_roles', $all_roles);
        $rolearray=array();
        $sum=6;
        foreach($editable_roles as $keyrol => $roledetails){
            switch($keyrol){
                case 'super_admin':
                    $index=1;
                    break;
                case 'administrator':
                    $index=2;
                    break;
                case 'editor':
                    $index=3;
                    break;
                case 'author':
                    $index=4;
                    break;
                case 'contributor':
                    $index=5;
                    break;
                case 'subscriber':
                    $index=6;
                    break;
                default:
                    $sum++;
                    $index=$sum;
            }
            $rolearray[$index]=array('key'=>$keyrol,'name'=>$roledetails['name']);
        }
        ksort($rolearray);
        return $rolearray;
    }
    function wp_get_editable_roles() {
        
        global $wp_roles;
        $all_roles = $wp_roles->roles;
        $editable_roles = apply_filters('editable_roles', $all_roles);
        $possible_values=array();
        foreach ( $all_roles as $role => $details ) {
            $name = translate_user_role($details['name'] );
            switch($role){
                case 'administrator':
                    $keyrole='switch_themes';
                    break;
                case 'editor':
                    $keyrole='moderate_comments';
                    break;
                case 'author':
                    $keyrole='upload_files';
                    break;
                case 'contributor':
                    $keyrole='edit_posts';
                    break;
                case 'subscriber':
                    $keyrole='read';
                    break;
                default:
                    $keyrole=$role;
            }
            $possible_values[$keyrole]=$name;

        }
        return $possible_values;
    }

    
}