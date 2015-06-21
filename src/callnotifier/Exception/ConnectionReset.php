<?php
namespace callnotifier;

/**
 * Since auerswald compact 3000 firmware 4.0N, the debug port is
 * closed every night. We need to re-connect in this case
 */
class Exception_ConnectionReset extends \Exception
{
}
?>