<?php
/**
 * File containing module definition
 *
 * @copyright Copyright (C) 1999-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPLv2
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
