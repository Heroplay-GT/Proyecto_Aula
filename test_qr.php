<?php
require 'vendor/autoload.php';

try {
    $qrCode = \Endroid\QrCode\QrCode::create('Test QR Code')
        ->setSize(300)
        ->setMargin(10);
    
    $writer = new \Endroid\QrCode\Writer\PngWriter();
    $result = $writer->write($qrCode);
    
    // Guardar en archivo
    $result->saveToFile(__DIR__.'/Media/QRCodes/test_qr.png');
    
    echo "QR generado correctamente en: ".__DIR__."/Media/QRCodes/test_qr.png";
} catch (Exception $e) {
    echo "Error: ".$e->getMessage();
}