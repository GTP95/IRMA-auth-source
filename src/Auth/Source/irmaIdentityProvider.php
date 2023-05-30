<?php
namespace SimpleSAML\Module\irmaidentity\Auth\Source;

//Class irmaIdentityProvider to authenticate the user using IRMA
use http\Env\Response;
//dependencies for qr-code generation
require_once __DIR__ . '../../../../vendor/autoload.php';

class irmaIdentityProvider extends \SimpleSAML\Auth\Source {

    public function __construct($info, $config) {
        parent::__construct($info, $config);
    }

    /**
     * @param array $state
     * @throws \SimpleSAML\Error\Exception
     */
    public function authenticate(array &$state): void
    {
        assert(is_array($state));
        $id=\SimpleSAML\Auth\State::saveState($state, 'irmaIdentityProvider:beforeRedirect');
        $url = \SimpleSAML\Module::getModuleURL('irmaidentity/irmaLoginPage.php');
        $httpUtils = new \SimpleSAML\Utils\HTTP();
        $httpUtils->redirectTrustedURL($url);
    }

}
?>

