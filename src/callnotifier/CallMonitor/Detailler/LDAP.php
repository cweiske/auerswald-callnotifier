<?php
namespace callnotifier;

class CallMonitor_Detailler_LDAP implements CallMonitor_Detailler
{
    public function __construct($ldapConfig)
    {
        $this->ldap = \Net_LDAP2::connect($ldapConfig);
        if (\PEAR::isError($this->ldap)) {
            throw new \Exception(
                'Could not connect to LDAP-server: ' . $this->ldap->getMessage()
            );
        }
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
        $filter = \Net_LDAP2_Filter::combine(
            'or',
            array(
                \Net_LDAP2_Filter::create('companyPhone', 'equals', $number),
                \Net_LDAP2_Filter::create('homePhone', 'equals', $number),
                \Net_LDAP2_Filter::create('mobile', 'equals', $number),
                \Net_LDAP2_Filter::create('otherPhone', 'equals', $number),
                \Net_LDAP2_Filter::create('telephoneNumber', 'equals', $number),
            )
        );
        $options = array(
            'scope' => 'sub',
            'attributes' => array('displayName', 'givenName', 'sn', 'cn')
        );

        $search = $this->ldap->search(null, $filter, $options);
        if (\PEAR::isError($search)) {
            throw new \Exception(
                'Error searching LDAP: ' . $search->getMessage()
            );
        }
        if ($search->count() == 0) {
            return null;
        }

        $arEntry = $search->shiftEntry()->getValues();
        if (isset($arEntry['displayName'])) {
            return $arEntry['displayName'];
        } else if (isset($arEntry['sn']) && $arEntry['givenName']) {
            return $arEntry['givenName'] . ' ' . $arEntry['sn'];
        } else if (isset($arEntry['cn'])) {
            return $arEntry['cn'];
        }
        return null;
    }

}

?>
