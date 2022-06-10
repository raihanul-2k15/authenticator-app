<?php

use OTPHP\TOTP;

require_once './load.php';

class TotpProfiles
{
    private static $tableName = 'totp_profiles';

    public $id;
    public $name;
    public $secret;
    public $hashAlgo;
    public $period;

    public function __construct($id, $name, $secret, $hashAlgo, $period)
    {
        $this->id = $id;
        $this->name = $name;
        $this->secret = $secret;
        $this->hashAlgo = $hashAlgo;
        $this->period = $period;
    }

    /**
     * Generate a 6 digit OTP
     */
    public function getOtp()
    {
        $otp = TOTP::create($this->secret, $this->period, $this->hashAlgo);
        return $otp->now();
    }

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        /** This array will contain all the TotpProfile (this class) objects */
        $ret = [];

        $sql = "SELECT * from " . self::$tableName . ";";
        $result = ($conn = getDbConn())->query($sql);
        if (!$result) {
            throw new \Exception("MySQL Query Error: " . $conn->error);
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ret[] = new self(
                    $row['id'],
                    $row['name'],
                    $row['secret'],
                    $row['hash_algo'],
                    $row['period']
                );
            }
        }

        return $ret;
    }

    /**
     * Insert a new profile into db
     *
     * @param string $name
     * @param string $secret
     * @param string $hashAlgo = 'sha1'
     * @param int $period = 30
     * @return void
     */
    public static function create($name, $secret, $hashAlgo = 'sha1', int $period = 30)
    {
        $sql = "INSERT INTO " . self::$tableName . "(name, secret, hash_algo, period) VALUES ('$name', '$secret', '$hashAlgo', $period);";
        $result = ($conn = getDbConn())->query($sql);
        if (!$result) {
            throw new \Exception("MySQL Query Error: " . $conn->error);
        }
    }

    /**
     * Delete a profile given it's ID
     *
     * @param int $id
     * @return void
     */
    public static function delete($id)
    {
        $sql = "DELETE FROM " . self::$tableName . " WHERE id = $id;";
        $result = ($conn = getDbConn())->query($sql);
        if (!$result) {
            throw new \Exception("MySQL Query Error: " . $conn->error);
        }
    }

    /**
     * Just to check if the OTP parameters (e.g secret) is in correct format. Primarily checks if secret key in base32 or not
     * Throws error if not valid
     * Validation logic is delegated to the OTPHP library... who will write this logic himself? lol
     *
     * @param string $name
     * @param string $secret
     * @param string $hashAlgo
     * @param int $period
     * @return void
     */
    public static function validate($name, $secret, $hashAlgo = 'sha1', int $period = 30)
    {
        try {
            $prof = new self(-1, $name, $secret, $hashAlgo, $period);
            $prof->getOtp();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
