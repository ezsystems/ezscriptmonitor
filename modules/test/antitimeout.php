<?php
/**
 * File containing antitimeout controller
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 */

// This will exit gracefully before timeout occurs


$startTime = time();
$executionTime = 0;
$phpTimeoutLimit = ini_get( 'max_execution_time' );

$tpl = eZTemplate::factory();

while ( true )
{
    $variable = 'text' + 42;

    $executionTime = time() - $startTime;

    if ( $executionTime > ( $phpTimeoutLimit * 0.8 ) )
    {
        $tpl->setVariable( 'execution_time', $executionTime );
        $tpl->setVariable( 'php_timeout_limit', $phpTimeoutLimit );
        break;
    }
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:test/antitimeout.tpl' );
return $Result;

?>
