<?php
namespace SimpleSAML\Module\irmaidentity\Auth\Source;

//Class irmaIdentityProvider to authenticate the user using IRMA
use http\Env\Response;
//dependencies for qr-code generation
require_once __DIR__ . '../../../../vendor/autoload.php';

class irmaIdentityProvider extends \SimpleSAML\Auth\Source {

    /**
     * Called by SimpleSAMLphp.
     * @param $info
     * @param $config
     */
    public function __construct($info, $config) {
        parent::__construct($info, $config);
    }

    /**
     * The module's entrypoint.
     * Called by SimpleSAMLphp when the user wants to authenticate using this authentication source.
     * @param array $state
     * @throws \SimpleSAML\Error\Exception
     */
    public function authenticate(array &$state): void
    {
        assert(is_array($state));
        $id=\SimpleSAML\Auth\State::saveState($state, 'irmaIdentityProvider:beforeRedirect');
        $url = \SimpleSAML\Module::getModuleURL('irmaidentity/irmaLoginPage.php');
        $httpUtils = new \SimpleSAML\Utils\HTTP();
        $httpUtils->redirectTrustedURL($url, array('authStateId' => $id));
    }

    /**
     * Called by irmaLoginPage.php to pass back the disclosed attribute
     * @param $attribute
     * @param $authStateId
     * @return void
     */
    public static function attributeCallback($attribute, $authStateId)
    {
        /* Retrieve the authentication state. */
        $state = \SimpleSAML\Auth\State::loadState($authStateId, 'irmaIdentityProvider:beforeRedirect');
        $attributeArray = array("participantId"=>$attribute);
        $state['Attributes'] = SimpleSAML\Utils\Attributes::normalizeAttributesArray($attributeArray);
        \SimpleSAML\Auth\Source::completeAuth($state);
    }

}
?>

