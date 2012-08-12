<?php
namespace callnotifier;

/**
 * Fetch caller names from a LDAP address book.
 *
 * The following attributes are searched:
 * - companyPhone
 * - homePhone
 * - mobile
 * - otherPhone
 * - telephoneNumber
 *
 * For the first result, the displayName is used if defined.
 * If it does not exist, the givenName + sn are used.
 *
 * Set "toName" or "fromName", depending on call type.
 *
 * Uses the Net_LDAP2 PEAR package
 */
class CallMonitor_Detailler_LDAP implements CallMonitor_Detailler
{
    /**
     * Create new ldap name resolver
     *
     * @param array $ldapConfig Array of Net_LDAP2 configuration parameters.
     *                          Some of those you might want to use:
     *                          - host   - LDAP server host name
     *                          - basedn - root DN that gets searched
     *                          - binddn - Username to authenticate with
     *                          - bindpw - Password for username
     */
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
            if (!isset($call->fromName) || $call->fromName === null) {
                $call->fromName = $this->loadName($call->from);
            }
        } else {
            if (!isset($call->toName) || $call->toName === null) {
                $call->toName = $this->loadName($call->to);
            }
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
