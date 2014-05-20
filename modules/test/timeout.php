<?php
/**
 * File containing timeout controller
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 */
// This will time out


while ( true )
{
    $variable = 'text' + 42;
}

$tpl = eZTemplate::factory();

$Result = array();
$Result['content'] = $tpl->fetch( 'design:test/timeout.tpl' );
return $Result;

?>
