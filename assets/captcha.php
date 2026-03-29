<?php
session_start();

// Demo Mode: Fixed Code
$captcha_code = "1357";
$_SESSION['captcha'] = $captcha_code;

// Set header for SVG
header('Content-Type: image/svg+xml');
header('Cache-Control: no-cache, no-store, must-revalidate');

// SVG Output
echo '<?xml version="1.0" standalone="no"?>';
?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="200" height="80" version="1.1" xmlns="http://www.w3.org/2000/svg">
    <!-- Background -->
    <rect width="200" height="80" style="fill:white;stroke:black;stroke-width:2" />
    
    <!-- Noise Lines -->
    <path d="M10 20 Q 90 90 180 20" stroke="gray" fill="transparent" stroke-width="2"/>
    <path d="M20 70 Q 100 0 190 70" stroke="gray" fill="transparent" stroke-width="2"/>
    <line x1="10" y1="10" x2="190" y2="70" style="stroke:gray;stroke-width:1" />
    <line x1="190" y1="10" x2="10" y2="70" style="stroke:gray;stroke-width:1" />

    <!-- Text 1 3 5 7 -->
    <text x="30" y="55" fill="black" font-family="Arial, sans-serif" font-size="40" font-weight="bold">1</text>
    <text x="70" y="45" fill="black" font-family="Arial, sans-serif" font-size="40" font-weight="bold">3</text>
    <text x="110" y="60" fill="black" font-family="Arial, sans-serif" font-size="40" font-weight="bold">5</text>
    <text x="150" y="50" fill="black" font-family="Arial, sans-serif" font-size="40" font-weight="bold">7</text>
</svg>
