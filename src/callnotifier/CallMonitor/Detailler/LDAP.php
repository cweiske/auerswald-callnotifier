<?php
namespace callnotifier;

class CallMonitor_Detailler_LDAP implements CallMonitor_Detailler
{
    public function __construct()
    {
    }

    public function loadCallDetails(CallMonitor_Call $call)
    {
        if ($call->type == CallMonitor_Call::INCOMING) {
            $call->fromName = $this->loadName($call->from);
        } else {
            $call->toName = $this->loadName($call->to);
        }
    }

    protected function loadName($number)
    {
        return 'foo';
    }

}

?>
