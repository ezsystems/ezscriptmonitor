#!/usr/bin/env php
<?php
// RELEASE: 20091112-1
//
// Created on: <10-Aug-2004 15:47:14 pk>
//
// Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file syncobjectattributes.php
*/

require 'autoload.php';


function updateClass( $classId, $scheduledScript )
{
    $cli = eZCLI::instance();

    /*
    // If the class is not stored yet, store it now
    $class = eZContentClass::fetch( $classId, true, eZContentClass::VERSION_STATUS_TEMPORARY );
    if ( $class )
    {
        $cli->output( "Storing class" );
        $class->storeDefined( $class->fetchAttributes() );
    }
    */

    // Fetch the stored class

    $class = eZContentClass::fetch( $classId, true, eZContentClass::VERSION_STATUS_MODIFIED );
    if ( !$class )
    {
        $cli->error( 'No class in a modified version status with ID: ' . $classId );
        return;
    }

    // Fetch attributes and definitions
    $attributes = $class->fetchAttributes( $classId, true, eZContentClass::VERSION_STATUS_MODIFIED );

    $oldClassAttributes = $class->fetchAttributes( $classId, true, eZContentClass::VERSION_STATUS_DEFINED );

    // Delete object attributes which have been removed.
    foreach ( $oldClassAttributes as $oldClassAttribute )
    {
        $attributeExist = false;
        $oldClassAttributeID = $oldClassAttribute->attribute( 'id' );
        foreach ( $attributes as $newClassAttribute )
        {
            if ( $oldClassAttributeID == $newClassAttribute->attribute( 'id' ) )
                $attributeExist = true;
        }
        if ( !$attributeExist )
        {
            foreach ( eZContentObjectAttribute::fetchSameClassAttributeIDList( $oldClassAttributeID ) as $objectAttribute )
            {
                $objectAttribute->removeThis( $objectAttribute->attribute( 'id' ) );
            }
        }
    }
    $class->storeVersioned( $attributes, eZContentClass::VERSION_STATUS_DEFINED );

    // Add object attributes which have been added.
    foreach ( $attributes as $newClassAttribute )
    {
        $attributeExist = false;
        foreach ( $oldClassAttributes as $oldClassAttribute )
        {
            if ( $oldClassAttribute->attribute( 'id' ) == $newClassAttribute->attribute( 'id' ) )
            {
                $attributeExist = true;
                break;
            }
        }
        if ( !$attributeExist )
        {
            $objects = null;
            $newClassAttribute->initializeObjectAttributes( $objects );
        }
    }

    if ( $scheduledScript !== false )
        $scheduledScript->updateProgress( 100 );
}


// Init script

$cli = eZCLI::instance();

$script = eZScript::instance( array( 'description' => ( "Synchronize object attributes with the new definition of a class\n\n" .
                                                        "Will add missing content object attributes, and remove redundant ones, for a given class.\n" .
                                                        "If the class is not given, it will check all classes.\n" .
                                                        "\n" .
                                                        'syncobjectattributes.php -s admin --classid=42' ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );
$script->startup();

$options = $script->getOptions( '[db-host:][db-user:][db-password:][db-database:][db-driver:][sql][classid:][admin-user:][scriptid:]',
                                '[name]',
                                array( 'db-host' => 'Database host',
                                       'db-user' => 'Database user',
                                       'db-password' => 'Database password',
                                       'db-database' => 'Database name',
                                       'db-driver' => 'Database driver',
                                       'sql' => 'Display sql queries',
                                       'classid' => 'ID of class to update',
                                       'admin-user' => 'Alternative login for the user to perform operation as',
                                       'scriptid' => 'Used by the Script Monitor extension, do not use manually' ) );
$script->initialize();

$dbUser = $options['db-user'] ? $options['db-user'] : false;
$dbPassword = $options['db-password'] ? $options['db-password'] : false;
$dbHost = $options['db-host'] ? $options['db-host'] : false;
$dbName = $options['db-database'] ? $options['db-database'] : false;
$dbImpl = $options['db-driver'] ? $options['db-driver'] : false;
$siteAccess = $options['siteaccess'] ? $options['siteaccess'] : false;

if ( $siteAccess )
{
    $cli = eZCLI::instance();
    if ( in_array( $siteAccess, eZINI::instance()->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' ) ) )
    {
        $cli->output( "Using siteaccess $siteAccess" );
    }
    else
    {
        $cli->notice( "Siteaccess $siteAccess does not exist, using default siteaccess" );
    }
}

$db = eZDB::instance();

if ( $dbHost or $dbName or $dbUser or $dbImpl )
{
    $params = array();
    if ( $dbHost !== false )
        $params['server'] = $dbHost;
    if ( $dbUser !== false )
    {
        $params['user'] = $dbUser;
        $params['password'] = '';
    }
    if ( $dbPassword !== false )
        $params['password'] = $dbPassword;
    if ( $dbName !== false )
        $params['database'] = $dbName;
    $db = eZDB::instance( $dbImpl, $params, true );
    eZDB::setInstance( $db );
}

$db->setIsSQLOutputEnabled( (bool) $options['sql'] );


// Log in admin user
$user = eZUser::fetchByName( isset( $options['admin-user'] ) ? $options['admin-user'] : 'admin' );
if ( $user )
    eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'id' ) );
else
{
    $cli->error( 'Could not fetch admin user object' );
    $script->shutdown( 1 );
    return;
}

// Take care of script monitoring
$scheduledScript = false;
if ( isset( $options['scriptid'] ) )
{
    $scheduledScript = eZScheduledScript::fetch( $options['scriptid'] );
}

// Do the update
if ( isset( $options['classid'] ) )
{
    updateClass( $options['classid'], $scheduledScript );
}
else
{

    $cli->notice( 'The classid parameter was not given, will check all classes.' );
    foreach ( eZContentClass::fetchList( eZContentClass::VERSION_STATUS_MODIFIED, false ) as $class )
    {
        $cli->output( 'Checking class with ID: ' . $class['id'] );
        updateClass( $class['id'], $scheduledScript );
    }

}

$script->shutdown();

?>
