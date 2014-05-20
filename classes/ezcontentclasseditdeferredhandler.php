<?php
//
// Definition of eZContentClassEditDeferredHandler class
//
// Created on: <11-Jan-2010 11:56:00 pa>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Publish
// SOFTWARE RELEASE: 4.3.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2014 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/**
 * Handler for content class editing.
 */
class eZContentClassEditDeferredHandler
{

    /**
     * Create a scheduled script that will store the modification made to an eZContentClass.
     *
     * @param eZContentClass Content class to be stored.
     * @param array[eZContentClassAttribute] Attributes of the new content class.
     * @param array Unordered view parameters
     */
    public function store( eZContentClass $class, array $attributes, array &$unorderedParameters )
    {
        $script = eZScheduledScript::create( 'syncobjectattributes.php',
                                             eZINI::instance( 'ezscriptmonitor.ini' )->variable( 'GeneralSettings', 'PhpCliCommand' ) .
                                             ' extension/ezscriptmonitor/bin/' . eZScheduledScript::SCRIPT_NAME_STRING .
                                             ' -s ' . eZScheduledScript::SITE_ACCESS_STRING . ' --classid=' . $class->attribute( 'id' ) );
        $script->store();
        $unorderedParameters['ScheduledScriptID'] = $script->attribute( 'id' );
        $class->storeVersioned( $attributes, eZContentClass::VERSION_STATUS_MODIFIED );
    }
}

?>