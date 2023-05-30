<?php
namespace SimpleSAML\Module\irmaidentity\Auth\Source;

//Class irmaIdentityProvider to authenticate the user using IRMA
use http\Env\Response;
//dependencies for qr-code generation
require_once __DIR__ . '../../../../vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
class irmaIdentityProvider extends \SimpleSAML\Auth\Source {
    public \IRMA\Requestor $requestor;

    public function __construct($info, $config) {
        parent::__construct($info, $config);
        $this->requestor = new \IRMA\Requestor("PEP", "PEP", "/var/simplesamlphp/cert/pep.cs.ru.nl.pem"); //TODO: load from config file
    }

    /**
     * @param array $state
     * @throws \SimpleSAML\Error\Exception
     */
    public function authenticate(array &$state): void
    {
        $json='{
  "@context": "https://irma.app/ld/request/disclosure/v2",
  "disclose": [
    [
        [ "irma-demo.PEP.id.id" ]
    ]
    ]
}';

//POST $json to localhost:8088/session
        $ch = curl_init('http://irmaserver:8088/session');   //TODO: load URL from config file
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result= curl_exec($ch);
        if ($result === false) {     //throw exception if curl_exec fails

            throw new \SimpleSAML\Error\Exception("Failed to get session from IRMA server: " . curl_error($ch));
        }
        $sessionPtr= json_decode($result, true)["sessionPtr"];
        $sessionPtr=json_encode($sessionPtr);
        $token=json_decode($result, true)["token"]; //get token from response, needed to query session status and get disclosed attributes
        curl_close($ch);


//encode $sessionPtr in a qr code
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($sessionPtr)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->labelText('Scan this QR code with the Yivi app')
            ->labelFont(new NotoSans(10))
            ->labelAlignment(new LabelAlignmentCenter())
            ->build();

        $dataUri = $qrCode->getDataUri();

//display the qr code
        echo '<HTML lang="en"><head></head><body><img src="'.$dataUri.'" alt="A QR-code to authenticate with the Yivi app"></body></HTML>';
        flush();    //flush the output buffer to the browser, so the user can see the QR code

        while (true) {
            //poll session to see if it is done
            $ch = curl_init('http://irmaserver:8088/session/'.$token."/status");   //TODO: load URL from config file
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));
            $result= curl_exec($ch);
            if ($result === false) {     //throw exception if curl_exec fails

                throw new \SimpleSAML\Error\Exception("Failed to get session status from IRMA server: " . curl_error($ch));
            }
            $status= json_decode($result, true);
            curl_close($ch);
            echo $status;
            if ($status == "DONE") {
                break;
            }

            sleep(1); //wait 1 second before polling again

        }
    }

}
?>

