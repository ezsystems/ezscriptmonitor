<?php
// This will time out

include_once( 'kernel/common/template.php' );

while ( true )
{
    $variable = 'text' + 42;
}

$tpl = templateInit();

$Result = array();
$Result['content'] = $tpl->fetch( 'design:test/timeout.tpl' );
return $Result;

?>
