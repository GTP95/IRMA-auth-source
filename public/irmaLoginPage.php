<?php
require_once '../vendor/autoload.php';
use http\Env\Response;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

//login page
echo '<HTML lang="en"><head><title>Yivi login</title></head><body><h1>Yivi login</h1>';
//request for the IRMA server
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
echo '<img src="'.$dataUri.'" alt="A QR-code to authenticate with the Yivi app"></body></HTML>';
flush();

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

    if ($status == "DONE") {
        break;
    }
    if ($status == "CANCELLED") {
        throw new \SimpleSAML\Error\Exception("IRMA session cancelled by user");
    }
    if($status == "TIMEOUT") {
        throw new \SimpleSAML\Error\Exception("IRMA session timed out");
    }

    sleep(1); //wait 1 second before polling again

}

//Here the session is done. Get the disclosed attributes, if any
$ch = curl_init('http://irmaserver:8088/session/'.$token."/result");   //TODO: load URL from config file
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json'
));
$result= curl_exec($ch);
if ($result === false) {     //throw exception if curl_exec fails

    throw new \SimpleSAML\Error\Exception("Failed to get session result from IRMA server: " . curl_error($ch));
}
curl_close($ch);

$decodedResult= json_decode($result, true);
if($decodedResult["proofStatus"] != "VALID") {
    throw new \SimpleSAML\Error\Exception("IRMA session result is not valid, the result is ".$decodedResult["proofStatus"]);
}

$disclosed= $decodedResult["disclosed"][0][0]["rawvalue"];
echo "<p>Disclosed attributes: $disclosed</p>";
var_dump($_GET['authStateId']);
flush();
SimpleSAML\Module\irmaidentity\Auth\Source\irmaIdentityProvider::attributeCallback($disclosed, $_GET['authStateId']);


