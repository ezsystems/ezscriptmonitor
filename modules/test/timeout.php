<?php
/**
 * File containing timeout controller
 *
 * @copyright Copyright (C) 1999-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPLv2
 *
 */
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
