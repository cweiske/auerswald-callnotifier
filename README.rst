Auerswald Callnotifier for COMpact 3000
***************************************

Connects to the debug port of your Auerswald Compact 3000 telephony switchboard
and listens for incoming calls.
It will notify you whenever a call comes in.


TODO
====
- multiple notification methods: XMPP, dreambox, notify-send
- ldap name resolution
- write a call list (who, when, how long)
- filter/exclude by local target


Issues
======
Ctrl+C does not send the disconnect command.
This is a problem with PHP since pcntl_signal handling and blocking sockets
do not work together. The signal will not be handled.
