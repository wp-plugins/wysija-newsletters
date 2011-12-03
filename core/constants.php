<?php

/*make the difference between frontend and backend routing*/
/*require what is needed for common purpose in backend such as backend menus*/

if(defined('WP_ADMIN')) {
    define("WYSIJA_SIDE","back");
}else define("WYSIJA_SIDE","front");

$plugin_name="WYSIJA";
$plugin_folder_name=dirname(dirname(plugin_basename(__FILE__)));
$current_folder=dirname(dirname(__FILE__));

if(!defined('DS')) define("DS", DIRECTORY_SEPARATOR);
define("WYSIJA", $plugin_name);

define("WYSIJA_PLG_DIR", dirname($current_folder).DS);
define("WYSIJA_DIR", $current_folder.DS);
define('WYSIJA_FILE',WYSIJA_DIR."index.php");
define("WYSIJA_URL",WP_PLUGIN_URL.'/'.strtolower($plugin_name));

define("WYSIJA_INC",WYSIJA_DIR."inc".DS);
define("WYSIJA_CORE",WYSIJA_DIR."core".DS);
define("WYSIJA_VIEWS",WYSIJA_DIR."views".DS);
define("WYSIJA_MODELS",WYSIJA_DIR."models".DS);
define("WYSIJA_HELPERS",WYSIJA_DIR."helpers".DS);
define("WYSIJA_CTRL",WYSIJA_DIR."controllers".DS);

define("WYSIJA_DIR_IMG",WYSIJA_DIR."img".DS);
define("WYSIJA_EDITOR_IMG",WYSIJA_URL."/img/");
// temporary / dirty way to access editor tools | <<< not so dirty after all. what do u think Benji Boy? =^.^=
define("WYSIJA_EDITOR_TOOLS",WYSIJA_DIR."tools".DS);
define("WYSIJA_DIR_THEMES",WYSIJA_DIR."themes".DS);
define("WYSIJA_EDITOR_THEMES",WYSIJA_URL."/themes/");


