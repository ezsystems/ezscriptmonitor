<?php

$Module = $Params['Module'];
$scriptID = $Params['ScriptID'];

$script = eZScheduledScript::fetch( $scriptID );
if ( is_object( $script ) )
    $scriptName = $script->attribute( 'name' );
else
    $scriptName = ezi18n( 'ezscriptmonitor', 'Script not found' );

require_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'module', $Module );
$tpl->setVariable( 'script', $script );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:scriptmonitor/view.tpl' );
$Result['path'] = array( array( 'url' => '/scriptmonitor/list/',
                                'text' => ezi18n( 'ezscriptmonitor', 'Script monitor' ) ),
                         array( 'url' => false,
                                'text' => $scriptName ) );

?>
