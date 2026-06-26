<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrService
{
    /**
     * Generate an SVG QR code for a given restaurant slug.
     */
    public function generateSvg(string $slug): string
    {
        // Generates the clean consumer-facing public routing endpoint URL
        $url = url("/menu/{$slug}");
        
        return QrCode::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->margin(1)
            ->generate($url);
    }
}