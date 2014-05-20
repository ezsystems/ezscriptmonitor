<?php
/**
 * File containing the eZScheduledScript class
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 */

class eZScheduledScript extends eZPersistentObject
{
    const SITE_ACCESS_STRING = '__SITE_ACCESS__'; // Magic string to be replaced with site access
    const SCRIPT_NAME_STRING = '__SCRIPT_NAME__'; // Magic string to be replaced with script name

    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_ACTIVE = 'active';
    const STATUS_DELAYED = 'delayed';
    const STATUS_DEAD = 'dead';
    const STATUS_COMPLETE = 'complete';

    const STATUS_ACTIVE_TIMEOUT = 300; // 5 minutes
    const STATUS_DELAYED_TIMEOUT = 900; // 15 minutes
    const STATUS_VISIBLE_TIMEOUT = 18000; // 5 hours
    const STATUS_PURGE_TIMEOUT = 86400; // 24 hours

    const PROGRESS_UNKNOWN = 999; // For scripts who don't know their own progress

    /*!
     Constructor
    */
    function __construct( $row )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \return a new object.
    */
    static function create( $name, $command, $userID = false )
    {
        if ( trim( $name ) == '' )
        {
            eZDebug::writeError( 'Empty name. You must supply a valid script name string.', 'ezscriptmonitor' );
            return false;
        }

        if ( trim( $command ) == '' )
        {
            eZDebug::writeError( 'Empty command. You must supply a valid command string.', 'ezscriptmonitor' );
            return false;
        }

        if ( !$userID )
        {
            $userID = eZUser::currentUserID();
        }

        $scriptMonitorIni = eZINI::instance( 'ezscriptmonitor.ini' );
        $scriptSiteAccess = $scriptMonitorIni->variable( 'GeneralSettings', 'ScriptSiteAccess' );
        $command = str_replace( self::SCRIPT_NAME_STRING, $name, $command );
        $command = str_replace( self::SITE_ACCESS_STRING, $scriptSiteAccess, $command );

        // Negative progress means not started yet
        return new self( array( 'name' => $name,
                                'command' => $command,
                                'last_report_timestamp' => time(),
                                'progress' => -1,
                                'user_id' => $userID ) );
    }

    /*!
     \return the persistent object definition.
    */
    static function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'process_id' => array( 'name' => 'ProcessID',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => false ),
                                         'name' => array( 'name' => 'Name',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'command' => array( 'name' => 'Command',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         'last_report_timestamp' => array( 'name' => 'LastReportTimestamp',
                                                                           'datatype' => 'integer',
                                                                           'default' => 0,
                                                                           'required' => false ),
                                         'progress' => array( 'name' => 'Progress',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => false ),
                                         'user_id' => array( 'name' => 'UserID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => false ) ),
                      'function_attributes' => array( 'process_id_text' => 'processIDText',
                                                      'status_text' => 'statusText',
                                                      'progress_text' => 'progressText' ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZScheduledScript',
                      'name' => 'ezscheduled_script' );
    }

    /*!
     \return a translated string of the process ID
    */
    function processIDText()
    {
        if ( is_numeric( $this->ProcessID ) and $this->ProcessID > 0 )
        {
            return $this->ProcessID;
        }

        return ezpI18n::tr( 'ezscriptmonitor', 'n/a' );
    }

    /*!
     \return the string constant describing the status
    */
    function statusText()
    {
        if ( $this->Progress == 100 )
        {
            return self::STATUS_COMPLETE;
        }

        if ( $this->Progress < 0 )
        {
            return self::STATUS_NOT_STARTED;
        }

        $timeSinceLastReport = time() - $this->LastReportTimestamp;

        if ( $timeSinceLastReport <= self::STATUS_ACTIVE_TIMEOUT )
        {
            return self::STATUS_ACTIVE;
        }

        if ( $timeSinceLastReport <= self::STATUS_DELAYED_TIMEOUT )
        {
            return self::STATUS_DELAYED;
        }

        return self::STATUS_DEAD;
    }

    /*!
     \return a translated string describing the progress
    */
    function progressText()
    {
        if ( $this->Progress == self::PROGRESS_UNKNOWN )
        {
            return ezpI18n::tr( 'ezscriptmonitor', 'Unknown' );
        }

        if ( $this->Progress < 0 )
        {
            return '0%';
        }

        if ( $this->Progress > 100 )
        {
            return '100%';
        }

        return $this->Progress . '%';
    }

    /*!
     \return the given script
    */
    static function fetch( $scriptID, $asObject = true )
    {
        $conditions = array( 'id' => $scriptID );

        return eZPersistentObject::fetchObject( eZScheduledScript::definition(),
                                                null, $conditions, $asObject );
    }

    /*!
     \return all current (recently active) scripts
    */
    static function fetchCurrentScripts( $asObject = true, $offset = false, $limit = false )
    {
        $conditions = array( 'last_report_timestamp' => array( '>', time() - self::STATUS_VISIBLE_TIMEOUT ) );

        $sorting = array( 'last_report_timestamp' => 'desc' );

        $limitation = null;
        if ( $offset !== false or $limit !== false )
        {
            $limitation = array( 'offset' => $offset, 'length' => $limit );
        }

        return eZPersistentObject::fetchObjectList( eZScheduledScript::definition(),
                                                    null, $conditions, $sorting, $limitation, $asObject );
    }

    /*!
     \return all old and completed scripts
    */
    static function fetchScriptsToPurge( $asObject = true, $offset = false, $limit = false )
    {
        $conditions = array( 'last_report_timestamp' => array( '<', time() - self::STATUS_PURGE_TIMEOUT ),
                             'progress' => 100 );

        $limitation = null;
        if ( $offset !== false or $limit !== false )
        {
            $limitation = array( 'offset' => $offset, 'length' => $limit );
        }

        return eZPersistentObject::fetchObjectList( eZScheduledScript::definition(),
                                                    null, $conditions, null, $limitation, $asObject );
    }

    /*!
     \return all scripts that have not started yet
    */
    static function fetchNotStartedScripts( $asObject = true, $offset = false, $limit = false )
    {
        $conditions = array( 'progress' => array( '<', 0 ) );

        $limitation = null;
        if ( $offset !== false or $limit !== false )
        {
            $limitation = array( 'offset' => $offset, 'length' => $limit );
        }

        return eZPersistentObject::fetchObjectList( eZScheduledScript::definition(),
                                                    null, $conditions, null, $limitation, $asObject );
    }

    /*!
     Stores the new percentage and updates the timestamp
    */
    function updateProgress( $progressPercentage )
    {
        $this->setAttribute( 'progress', (int)$progressPercentage );
        $this->setAttribute( 'last_report_timestamp', time() );
        $this->store();
    }

}

?>
