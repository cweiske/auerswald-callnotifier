***************************************
Auerswald Callnotifier for COMpact 3000
***************************************

Connects to the debug port of your Auerswald Compact 3000 telephony switchboard
and listens for incoming calls.
It will notify you whenever a call comes in.

It also listens for outgoing calls if you want that, and you can log
incoming and/or outgoing calls into a file or a database.

Callnotifier is able to resolve the names of calls via LDAP and the
caller location city names via OpenGeoDb.

Blog post: https://cweiske.de/tagebuch/auerswald-callnotifier.htm

.. contents::


============
Installation
============

1. Clone the git repository
2. Install dependencies
3. Configure the tool
4. Run ``./callnotifier.php``


Dependencies
============
- PEAR's Console_CommandLine package
- PEAR's Net_LDAP2 package for LDAP name resolution
- curl for the dreambox notifier


Systemd service
===============

1. Copy ``scripts/systemd/auerswald-callnotifier.service`` to ``/etc/systemd/system/``
2. Adjust user and group name and callnotifier path
3. Enable the service::

    $ systemctl daemon-reload
    $ systemctl enable auerswald-callnotifier
    $ systemctl start auerswald-callnotifier
    $ systemctl status auerswald-callnotifier


=============
Configuration
=============

Compact 3000
============
Enable the debug port by activating

- Configuration

  - Server configuration

    -  D channel output via IP D channel output via IP on/off

German:

- Einrichtung

  - Serverkonfiguration

    - D-Kanal über IP ausgeben D-Kanal über IP ausgeben ein-/ausschalten


Callnotifier
============
Copy ``data/callnotifier.config.php.dist`` to ``data/callnotifier.config.php``
(same name, just no ``.dist``), open it in a text editor and adjust it to
your needs.

It contains examples for every plugin.
Adjust them as you need and remove the ones you do not need.



Call types
==========
Logging plugins allow you to specify when calls shall be logged:

- ``startingCall`` - when the call is coming in, and the telephone rings
- ``finishedCall`` - when the call has ended

Furthermore you may decide which calls to log:

- ``i`` - Log incoming calls only
- ``o`` - Log outgoing calls only
- ``io`` - Log both incoming and outgoing calls


=======
Plugins
=======
There are two types of plugins: Detailler and Logger.
Detaillers load additional details to a call - e.g. a name - and loggers
can do anything, e.g. write a log file or send a XMPP message.

Detailler
=========
Plugins that fetch additional details to calls are named "detailler".

CallMonitor_Detailler_LDAP
--------------------------
The plugin determines the name for a telephone number by looking up a
LDAP directory.

It retrieves the name of the caller for incoming calls, and the name
of the telephonee for outgoing calls.

It searches the following LDAP attributes:

- ``companyPhone``
- ``homePhone``
- ``mobile``
- ``otherPhone``
- ``telephoneNumber``

Example configuration::

    $callMonitor->addDetailler(
        new CallMonitor_Detailler_LDAP(
            array(
                'host' => 'ldap.home.cweiske.de',
                'basedn' => 'ou=adressbuch,dc=cweiske,dc=de',
                'binddn' => 'cn=readonly,ou=users,dc=cweiske,dc=de',
                'bindpw' => 'readonly'
            )
        )
    );


CallMonitor_Detailler_OpenGeoDb
-------------------------------
The plugin determines the location (city) for a telephone number by
checking the area code (prefix number) against a OpenGeoDB SQL database.

If several locations share the same area code, the one with the most inhabitants
is used.

If you use this plugin, you need to run ``docs/opengeodb-create-my_orte.sql``
on the OpenGeoDB database to create an indexed table with all relevant
information needed by the plugin.

Example configuration::

    $callMonitor->addDetailler(
        new CallMonitor_Detailler_OpenGeoDb(
            'mysql:host=dojo;dbname=opengeodb',
            'opengeodb-read',
            'opengeodb'
        )
    );


Logger
======
Logger handle react on events like incoming or outgoing calls,
if they start or finish, or on any data received from the ISDN bus.

Available logger:

CallDb
  Log calls in a SQL database
CallDreambox
  Send messages on incoming calls to the DreamBox satellite
  receiver
CallEcho
  Log to the command line. Helpful for debugging.
CallFile
  Log finished calls into a text file
CallFileTop
  Log finished calls into a text file, newest on top
CallNotifySend
  Use the unix ``notify-send`` command on starting and finished calls
CallSendXmpp
  Send an XMPP headline message for incoming calls to one or multiple
  users.


============
Known issues
============
Ctrl+C does not send the disconnect command.
This is a problem with PHP since pcntl_signal handling and blocking sockets
do not work together. The signal will not be handled.


=======
License
=======
Auerswald callnotifier is licensed under the terms of the GPLv3 or later.


======
Source
======
Original git website: https://git.cweiske.de/auerswald-callnotifier.git

Mirror: https://github.com/cweiske/auerswald-callnotifier
