<?php

function splitPacket($rawData, $delimiter) {
    if ($delimiter === null || $delimiter === '') {
        return [$rawData];
    }

    // 1. Handle * as end marker
    $starPos = strpos($rawData, '*');
    if ($starPos !== false) {
        $rawData = substr($rawData, 0, $starPos);
    }

    $parts = [];
    $current = '';
    $depth = 0;
    
    for ($i = 0; $i < strlen($rawData); $i++) {
        $char = $rawData[$i];
        
        if ($char === '(') {
            $depth++;
            $current .= $char;
        } elseif ($char === ')') {
            $depth--;
            $current .= $char;
        } elseif ($char === $delimiter && $depth === 0) {
            $parts[] = $current;
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    $parts[] = $current;
    
    return $parts;
}

$packet = '$NMP,JSDE14A,2.2.4,NR,1,L,860269069192870,0,1,27042026,111305,30.870540,N,75.8557434,E,000.0,281.62,31,256.671,0.86,0.46,airtel,1,1,12.6,4.0,0,C,22,404,02,1E84,7D3440C,C3FD10F,1E84,19,7D3440D,1E84,19,C3FD10E,1E84,19,0,0,0,0010,00,000658,00.0,00.2,0,(0,0,0)*10';
$delimiter = ',';

echo "Original Packet: $packet\n";
echo "Delimiter: $delimiter\n\n";

$parts = splitPacket($packet, $delimiter);

echo "Parts count: " . count($parts) . "\n";
foreach ($parts as $i => $part) {
    echo "[$i] => $part\n";
}
