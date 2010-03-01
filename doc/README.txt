README
------
Gunnstein Lye <gl@ez.no>
v0.4

This extension aims to avoid timeout problems and database corruption by
moving long running processes from the GUI to the background.

For background information, see: doc/ezscriptmonitor.txt

For installation instructions, see: doc/INSTALL.txt


Currently supported features

- Class editing: When a content class is stored, all content objects must be
  updated with the new class information. This may take a lot of time when
  there are many objects of the given class. The extension moves this process
  to the script monitor - either automatically, by detecting that it is about
  to time out, or manually, when the user indicates this before storing. Class
  editing is also improved when the script monitor extension is not installed.
  In this case it will alert the user to run the class update script manually.

- Subtree removal: Removing a subtree with many nodes may time out. The
  current code will not start the removal in such cases, and instead tells the
  user to use the ezsubtreeremove.php script instead. The patch adds
  scheduling support to the script, and informs the user that removal has been
  scheduled to run in the background.


Possible future features

- Subtree copying
- Trash emptying
- Class removal


Extending the currently supported features

To add script monitoring support to other potentially long running processes
of the system, the following must be done:

- The process should be made interruptible, if it isn't already. I.e. it
  should be possible to interrupt the process without harming it, and also to
  resume it at a later time. If this is not possible, automatic timeout
  protection can not be done. In that case you should instead make sure the
  process is never started by the GUI, only by the script monitor.

- The process should be protected against timeouts, see
  modules/test/antitimeout.php for a simple example.

- You must make a CLI script that performs the long running process. In the
  case that the timeout protection is triggered, or an uninterruptible process
  should be run, the GUI module must schedule this script for background
  execution like this:

  $script = eZScheduledScript::create( 'your_script.php',
                                       'path/to/your/script/' . eZScheduledScript::SCRIPT_NAME_STRING .
                                       ' -s ' . eZScheduledScript::SITE_ACCESS_STRING .
                                       ' --your-script-parameters=42',
                                       eZScheduledScript::TYPE_PHP );
  $script->store();

  Then the GUI module should return and inform the user that the process will
  be executed in the background, and provide a link to the script monitor page
  for this script: http://host/(siteaccess)/scriptmonitor/view/(script id)
  The script ID is given by $script->attribute( 'id' );

- Your CLI script must support the --scriptid parameter, which will be set by
  the scheduler. At the beginning of the script you must read this parameter
  and fetch the scheduled script object, like this:

  $scheduledScript = false;
  if ( isset( $options['scriptid'] ) and
       in_array( 'ezscriptmonitor', eZExtension::activeExtensions() ) and
       class_exists( 'eZScheduledScript' ) )
  {
      $scriptID = $options['scriptid'];
      $scheduledScript = eZScheduledScript::fetch( $scriptID );
  }

- At reasonable intervals (no less than every 5 minutes, preferably more
  often) your script must inform the script monitor about its progress, like
  this:

  if ( $scheduledScript )
  {
      $scheduledScript->updateProgress( $progressPercentage );
  }

  The progress is a value between 0 and 100. If the progress cannot be
  determined, you must instead report eZScheduledScript::PROGRESS_UNKNOWN. It
  is important to do this, so that the script monitor knows the script is
  still active. When the script is done, you must report 100% progress.
