<?php
namespace SimpleSAML\Module\IRMAidp\Auth\Source;

//Class irmaIdentityProvider to authenticate the user using IRMA
class irmaIdentityProvider extends \SimpleSAML\Auth\Source {
    public \IRMA\Requestor $requestor;

    //class constructor
    public function __construct($info, $config) {
        parent::__construct($info, $config);
        $this->requestor = new \IRMA\Requestor("PEP", "PEP", "testkey.pem"); //TODO: load form config file
    }

    public function authenticate(){
        try {
            $jwt = $this->requestor->getVerificationJwt([
                [
                    "label" => "PEP study participant id",
                    "attributes" => ["irma-demo.PEP.id"]
                ]
            ]);
            var_dump($jwt);

        } catch (\Exception $e) {
            throw new \SimpleSAML\Error\Exception("Could not get IRMA JWT: " . $e->getMessage());
        }
    }

}
?>

