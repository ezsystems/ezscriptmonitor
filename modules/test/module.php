<?php
/**
 * File containing module definition
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 */

$Module = array( 'name' => 'Test',
                 'variable_params' => true );

$ViewList = array();

$ViewList['timeout'] = array(
    'script' => 'timeout.php',
    );

$ViewList['antitimeout'] = array(
    'script' => 'antitimeout.php',
    );

?>
