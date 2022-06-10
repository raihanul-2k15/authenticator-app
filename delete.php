<?php

require_once './model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return errorResponse('Please use POST request', 'Request method not supported', 405);
}

$id = $_POST['id'];

try {
    TotpProfiles::delete($id);
} catch (\Exception $e) {
    errorResponse('Server Error', $e->getMessage());
    exit;
}

successResponse('Deleted successfully');
