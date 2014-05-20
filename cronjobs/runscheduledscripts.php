<?php
/**
 * File containing runscheduledscripts.php cronjob.
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 */

// Fetch all scripts that have not been started yet, and start them
$notStartedScripts = eZScheduledScript::fetchNotStartedScripts();
foreach ( $notStartedScripts as $notStartedScript )
{
    if ( !$isQuiet )
    {
        $cli->output( 'Starting scheduled script in the background: ' . $notStartedScript->attribute( 'command' ) );
    }

    // Set progress to zero here. Do not trust that the script will do it. If it fails to do so, it will be executed over and over again forever.
    $notStartedScript->setAttribute( 'progress', 0 );
    $notStartedScript->store();

    // Start the script in the background (will not wait for it to complete)
    execInBackground( $notStartedScript->attribute( 'command' ) . ' --scriptid=' . $notStartedScript->attribute( 'id' ) );
}

// Fetch all old and completed scripts, and remove them
$scriptsToPurge = eZScheduledScript::fetchScriptsToPurge();
foreach( $scriptsToPurge as $oldScript )
{
    $oldScript->remove();
}


// Execute a process in the background, should work on both Linux and Windows
// From php doc:
// http://no.php.net/manual/en/function.exec.php#86329
function execInBackground( $command )
{
    if ( substr( php_uname(), 0, 7 ) == 'Windows' )
    {
        pclose( popen( 'start /B ' . $command, 'r' ) );
    }
    else
    {
        exec( $command . ' > /dev/null &' );
    }
}

?>
