<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace Garden\Password;



class PhpPassword implements IPassword {
    /**
     * @var int One of the `PASSWORD_*` constants supplied to {@link password_hash()}.
     */
    protected $algorithm;

    /**
     * Initialize an instance of this class.
     *
     * @param int $algorithm The crypt password to use when hashing passwords.
     */
    public function __construct($algorithm = PASSWORD_DEFAULT) {
        $this->algorithm = $algorithm;
    }


    /**
     * Hashes a plaintext password.
     *
     * @param string $password The password to hash.
     * @return string Returns the hashed password.
     */
    public function hash($password) {
        return password_hash($password, $this->algorithm);
    }

    /**
     * Checks if a given password hash needs to be re-hashed to to a stronger algorithm.
     *
     * @param string $hash The hash to check.
     * @return bool Returns `true`
     */
    public function needsRehash($hash) {
        return password_needs_rehash($hash, $this->algorithm);
    }

    /**
     * {@inheritdoc}
     */
    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}
