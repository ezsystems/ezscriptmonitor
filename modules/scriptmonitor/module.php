<?php

$Module = array( 'name' => 'Script monitor' );

$ViewList = array();

$ViewList['list'] = array(
    'script' => 'list.php',
    'default_navigation_part' => 'ezsetupnavigationpart'
    );

$ViewList['view'] = array(
    'script' => 'view.php',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'params' => array( 'ScriptID' )
    );

?>
