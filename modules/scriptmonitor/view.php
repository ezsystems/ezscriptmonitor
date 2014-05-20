<?php
/**
 * File containing view controller
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 */

$Module = $Params['Module'];
$scriptID = $Params['ScriptID'];

$script = eZScheduledScript::fetch( $scriptID );
if ( is_object( $script ) )
    $scriptName = $script->attribute( 'name' );
else
    $scriptName = ezpI18n::tr( 'ezscriptmonitor', 'Script not found' );

$tpl = eZTemplate::factory();
$tpl->setVariable( 'module', $Module );
$tpl->setVariable( 'script', $script );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:scriptmonitor/view.tpl' );
$Result['path'] = array( array( 'url' => '/scriptmonitor/list/',
                                'text' => ezpI18n::tr( 'ezscriptmonitor', 'Script monitor' ) ),
                         array( 'url' => false,
                                'text' => $scriptName ) );

?>
