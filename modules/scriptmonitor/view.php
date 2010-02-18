<?php

$Module = $Params['Module'];
$scriptID = $Params['ScriptID'];

$script = eZScheduledScript::fetch( $scriptID );
if ( is_object( $script ) )
    $scriptName = $script->attribute( 'name' );
else
    $scriptName = ezpI18n::translate( 'ezscriptmonitor', 'Script not found' );

require_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'module', $Module );
$tpl->setVariable( 'script', $script );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:scriptmonitor/view.tpl' );
$Result['path'] = array( array( 'url' => '/scriptmonitor/list/',
                                'text' => ezpI18n::translate( 'ezscriptmonitor', 'Script monitor' ) ),
                         array( 'url' => false,
                                'text' => $scriptName ) );

?>
