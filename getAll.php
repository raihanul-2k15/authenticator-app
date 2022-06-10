<?php

require_once './model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    return errorResponse('Please use GET request', 'Request method not supported', 405);
}

try {
    $profiles = TotpProfiles::getAll();
} catch (\Exception $e) {
    errorResponse('Server Error', $e->getMessage());
    exit - 1;
}

// $profiles is array of TotpProfile Object
// but we need array that can be converted to json for frontend datatable
$ret = [];
foreach ($profiles as $p) {
    try {
        $otp = $p->getOtp();
    } catch (\Exception $e) {
        // if somehow a bad (non base32) string gets inserted into the secret in mysql
        // make sure code not breaks and other profiles are still visible to the frontend
        $otp = "Invalid Secret";
    }
    $ret[] = [
        'id' => $p->id,
        'name' => $p->name,
        'secret' => $p->secret,
        'hashAlgo' => $p->hashAlgo,
        'period' => $p->period,
        'otp' => $otp,
    ];
}

successResponse('Data', $ret);
