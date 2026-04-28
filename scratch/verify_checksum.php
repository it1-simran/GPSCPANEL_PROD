<?php
$packet = '$NMP,JSDE14A,2.2.4,NR,1,L,490154203237518,0,1,28042026,092421,28.642400,N,77.230500,E,000.0,22.00,90,270.313,0.84,0.50,airtel,1,1,12.6,4.0,0,C,31,404,02,1E84,DC6711F,DC45021,1E84,35,7D3440C,1E84,23,C3FD10F,1E84,20,7D3440D,1E84,20,0010,00,000535,00.0,00.1,0,(0,0,0)*21';

$payload = explode('*', $packet)[0];
if ($payload[0] === '$') {
    $payload = substr($payload, 1);
}

$checksum = 0;
for ($i = 0; $i < strlen($payload); $i++) {
    $checksum ^= ord($payload[$i]);
}

$hex = str_pad(strtoupper(dechex($checksum)), 2, '0', STR_PAD_LEFT);
echo "Payload: $payload\n";
echo "Calculated Checksum: $hex\n";
echo "Expected Checksum: 21\n";
if ($hex === '21') {
    echo "MATCH!\n";
} else {
    echo "MISMATCH!\n";
}
