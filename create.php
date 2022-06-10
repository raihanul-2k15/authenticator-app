<?php

require_once './model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return errorResponse('Please use POST request', 'Request method not supported', 405);
}

$link = trim($_POST['link'] ?? '');
$name = trim($_POST['name'] ?? '');
$secret = trim($_POST['secret'] ?? '');

// lowercase and spaces aren't supported in base32 format
$secret = strtoupper(preg_replace('/\s/', '', $secret));

if ($link) {
    // if user provided the link scanned from QR code, then use it to extract the OTP parameters
    try {
        $otpObj = \OTPHP\Factory::loadFromProvisioningUri($link);
        $name = $otpObj->getIssuer() . ' ' . $otpObj->getLabel();
        $secret = $otpObj->getSecret();
    } catch (\Exception $e) {
        errorResponse('OTP QR link validation failed', $e->getMessage() . ' ' . $link);
        exit;
    }
}

if (!$name || !$secret) {
    return errorResponse('name and secret required', 'name and secret must not be empty', 400);
}

try {
    TotpProfiles::validate($name, $secret);
} catch (\Exception $e) {
    errorResponse('Profile validation error', $e->getMessage());
    exit;
}

try {
    TotpProfiles::create($name, $secret);
} catch (\Exception $e) {
    errorResponse('Server Error', $e->getMessage());
    exit - 1;
}

successResponse('Created successfully');
