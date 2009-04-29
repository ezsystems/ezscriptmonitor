<?php
// This will exit gracefully before timeout occurs

include_once( 'kernel/common/template.php' );

$startTime = time();
$executionTime = 0;
$phpTimeoutLimit = ini_get( 'max_execution_time' );

$tpl = templateInit();

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
