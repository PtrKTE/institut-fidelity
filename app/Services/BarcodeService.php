<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    public function generateCardNumber(): string
    {
        return strtoupper(uniqid('FID'));
    }

    public function generateBarcode(): string
    {
        return bin2hex(random_bytes(8));
    }

    public function generateBarcodePNG(string $code): string
    {
        $generator = new BarcodeGeneratorPNG();
        return base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128));
    }
}
