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

/*! \file addmissingobjectattributes.php
*/

require 'autoload.php';


function updateClass( $classId )
{
    global $cli, $script, $db, $scheduledScript;

    // If the class is not stored yet, store it now
    $class = eZContentClass::fetch( $classId, true, eZContentClass::VERSION_STATUS_TEMPORARY );
    if ( $class )
    {
        $cli->output( "Storing class" );
        $class->storeDefined( $class->fetchAttributes() );
    }

    // Fetch the stored class
    $class = eZContentClass::fetch( $classId );
    if ( !$class )
    {
        $cli->error( 'Could not fetch class with ID: ' . $classId );
        return;
    }
    $classAttributes = $class->fetchAttributes();
    $classAttributeIDs = array();
    foreach ( $classAttributes as $classAttribute )
    {
        $classAttributeIDs[] = $classAttribute->attribute( 'id' );
    }

    $objectCount = eZContentObject::fetchSameClassListCount( $classId );
    $cli->output( 'Number of objects to be processed: ' . $objectCount );

    $counter = 0;
    $offset = 0;
    $limit = 100;
    $objects = eZContentObject::fetchSameClassList( $classId, true, $offset, $limit );

    // Add and/or remove attributes for all versions and translations of all objects of this class
    while ( count( $objects ) > 0 )
    {
        // Run a transaction per $limit objects
        $db->begin();

        foreach ( $objects as $object )
        {
            $contentObjectID = $object->attribute( 'id' );
            $objectVersions = $object->versions();
            foreach ( $objectVersions as $objectVersion )
            {
                $versionID = $objectVersion->attribute( 'version' );
                $translations = $objectVersion->translations();
                foreach ( $translations as $translation )
                {
                    $translationName = $translation->attribute( 'language_code' );

                    // Class attribute IDs of object attributes (not necessarily the same as those in the class, hence the manual sql)
                    $objectClassAttributeIDs = array();
                    $rows = $db->arrayQuery( "SELECT id,contentclassattribute_id, data_type_string
                                              FROM ezcontentobject_attribute
                                              WHERE contentobject_id = '$contentObjectID' AND
                                                    version = '$versionID' AND
                                                    language_code='$translationName'" );
                    foreach ( $rows as $row )
                    {
                        $objectClassAttributeIDs[ $row['id'] ] = $row['contentclassattribute_id'];
                    }

                    // Quick array diffs
                    $attributesToRemove = array_diff( $objectClassAttributeIDs, $classAttributeIDs ); // Present in the object, not in the class
                    $attributesToAdd = array_diff( $classAttributeIDs, $objectClassAttributeIDs ); // Present in the class, not in the object

                    // Remove old attributes
                    foreach ( $attributesToRemove as $objectAttributeID => $classAttributeID )
                    {
                        $objectAttribute = eZContentObjectAttribute::fetch( $objectAttributeID, $versionID );
                        if ( !is_object( $objectAttribute ) )
                            continue;
                        $objectAttribute->remove( $objectAttributeID );
                    }

                    // Add new attributes
                    foreach ( $attributesToAdd as $classAttributeID )
                    {
                        $objectAttribute = eZContentObjectAttribute::create( $classAttributeID, $contentObjectID, $versionID, $translationName );
                        if ( !is_object( $objectAttribute ) )
                            continue;
                        $objectAttribute->setAttribute( 'language_code', $translationName );
                        $objectAttribute->initialize();
                        $objectAttribute->store();
                        $objectAttribute->postInitialize();
                    }
                }
            }

            // Progress bar and Script Monitor progress
            $cli->output( '.', false );
            $counter++;
            if ( $counter % 70 == 0 or $counter >= $objectCount )
            {
                $progressPercentage = ( $counter / $objectCount ) * 100;
                $cli->output( sprintf( ' %01.1f %%', $progressPercentage ) );

                if ( $scheduledScript )
                {
                    $scheduledScript->updateProgress( $progressPercentage );
                }
            }
        }

        $db->commit();

        $offset += $limit;
        $objects = eZContentObject::fetchSameClassList( $classId, true, $offset, $limit );
    }

    // Set the object name to the first attribute, if not set
    $classAttributes = $class->fetchAttributes();

    // Fetch the first attribute
    if ( count( $classAttributes ) > 0 && trim( $class->attribute( 'contentobject_name' ) ) == '' )
    {
        $db->begin();
        $identifier = $classAttributes[0]->attribute( 'identifier' );
        $identifier = '<' . $identifier . '>';
        $class->setAttribute( 'contentobject_name', $identifier );
        $class->store();
        $db->commit();
    }
}


// Init script

$cli = eZCLI::instance();
$endl = $cli->endlineString();

$script = eZScript::instance( array( 'description' => ( "Add missing object attributes\n\n" .
                                                        "Will add missing content object attributes, and remove redundant ones, for a given class.\n" .
                                                        "If the class is not given, it will check all classes.\n" .
                                                        "\n" .
                                                        'addmissingobjectattributes.php -s admin --classid=42' ),
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
$showSQL = $options['sql'] ? true : false;
$siteAccess = $options['siteaccess'] ? $options['siteaccess'] : false;

if ( $siteAccess )
{
    changeSiteAccessSetting( $siteAccess );
}

function changeSiteAccessSetting( $siteAccess )
{
    $cli = eZCLI::instance();
    if ( file_exists( 'settings/siteaccess/' . $siteAccess ) )
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

$db->setIsSQLOutputEnabled( $showSQL );


// Log in admin user
if ( isset( $options['admin-user'] ) )
{
    $adminUser = $options['admin-user'];
}
else
{
    $adminUser = 'admin';
}
$user = eZUser::fetchByName( $adminUser );
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
if ( isset( $options['scriptid'] ) and
     in_array( 'ezscriptmonitor', eZExtension::activeExtensions() ) and
     class_exists( 'eZScheduledScript' ) )
{
    $scriptID = $options['scriptid'];
    $scheduledScript = eZScheduledScript::fetch( $scriptID );
}

// Do the update
if ( isset( $options['classid'] ) )
{
    updateClass( $options['classid'] );
}
else
{
    $cli->notice( 'The classid parameter was not given, will check all classes.' );
    $classes = eZContentClass::fetchAllClasses( false );
    foreach ( $classes as $class )
    {
        $cli->notice( 'Checking class ' . $class['id'] . ': ' . $class['name'] );
        updateClass( $class['id'] );
    }
}

$script->shutdown();

?>
