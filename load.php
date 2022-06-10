<?php

require_once 'vendor/autoload.php';

/** helper method to send a success json response */
function successResponse($msg, $data = [])
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $msg,
        'data' => $data,
    ]);
}

/** helper method to send a failure json response */
function errorResponse($msg, $error, $code = 400)
{
    header('Content-Type: application/json', true, $code);
    echo json_encode([
        'success' => false,
        'message' => $msg,
        'error' => $error,
    ]);
}

/**
 * When an error occurs, immediately stop executing script and send an error response with the error message
 */
set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    errorResponse('Server error', $msg, 500);
    exit;
});

/** it will load the variables from .env file to $_ENV superglobal */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = null;

/**
 * Get the database connection object to perform db queries
 *
 * @return mysqli
 */
function getDbConn()
{
    global $conn;

    /**
     * If this func is called more than once in same request (I don't think it's called at this stage of project)
     * no need to connect to db again.. just reuse the conn object
     */
    if ($conn !== null) {
        return $conn;
    }

    $conn = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        $_ENV['DB_DATABASE'],
        $_ENV['DB_PORT'],
    );

    if ($conn->connect_error) {
        errorResponse("MySQL connect error", $conn->connect_error);
        exit;
    }

    /** Run the migration sql on every connection.. to create table if not exists already */
    $sql = file_get_contents(__DIR__ . '/migration.sql');
    $result = $conn->query($sql);
    if (!$result) {
        errorResponse("MySQL query error", $conn->error);
        exit;
    }

    return $conn;
}
