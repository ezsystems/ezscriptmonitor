<?php

$Module = $Params['Module'];

$scripts = eZScheduledScript::fetchCurrentScripts();

require_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'module', $Module );
$tpl->setVariable( 'scripts', $scripts );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:scriptmonitor/list.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => eZi18n::translate( 'ezscriptmonitor', 'Script monitor' ) ) );

?>
