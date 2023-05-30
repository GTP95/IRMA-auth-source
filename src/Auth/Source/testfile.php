<?php
namespace src;
//dependencies for qr-code generation
require_once __DIR__ . '../../../../vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


$json='{
  "@context": "https://irma.app/ld/request/disclosure/v2",
  "disclose": [
    [
        [ "irma-demo.PEP.id.id" ]
    ]
    ]
}';

//POST $json to localhost:8088/session
$ch = curl_init('http://localhost:8088/session');   //TODO: load URL from config file
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json'
));
$result= curl_exec($ch);
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
echo '<HTML lang="en"><img src="'.$dataUri.'" alt="A QR-code to authenticate with the Yivi app">';



