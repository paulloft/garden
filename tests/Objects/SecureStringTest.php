<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

use Garden\SecureString;

class SecureStringTest extends PHPUnit_Framework_TestCase {

    /**
     * Test a set of data against a spec.
     *
     * @param mixed $data The data to test.
     * @param array $spec A secure string spec.
     * @dataProvider provideDataAndSpecs
     */
    public function testSpec($data, array $spec) {
        $ss = new SecureString();

        $this->assertNotNull($data);
        $encoded = $ss->encode($data, $spec, true);
        $decoded = $ss->decode($encoded, $spec, true);

        $this->assertEquals($data, $decoded);
    }

    /**
     * Test a bad password when decoding.
     *
     * @param mixed $data The data to test.
     * @param array $spec A secure string spec.
     * @dataProvider provideDataAndSpecs
     * @expectedException \Exception
     * @expectedExceptionCode 403
     */
    public function testBadPasswords($data, array $spec) {
        $ss = new SecureString();

        $this->assertNotNull($data);

        $encoded = $ss->encode($data, $spec, true);

        foreach ($spec as &$password) {
            $password = uniqid('bad', true);
        }

        $null = $ss->decode($encoded, $spec, false);
        $this->assertNull($null);

        $decoded = $ss->decode($encoded, $spec, true);
    }

    /**
     * Test a missing password when decoding.
     *
     * @param mixed $data The data to test.
     * @param array $spec A secure string spec.
     * @dataProvider provideDataAndSpecs
     * @expectedException \Exception
     * @expectedExceptionCode 403
     */
    public function testMissingDecodePassword($data, $spec) {
        $ss = new SecureString();

        $this->assertNotNull($data);

        $encoded = $ss->encode($data, $spec, true);

        $null = $ss->decode($encoded, [], false);
        $this->assertNull($null);

        $decoded = $ss->decode($encoded, [], true);
    }

    /**
     * Test encoding a string with a missing spec.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 400
     */
    public function testUnsupportedSpecEncode() {
        $ss = new SecureString();

        $data = 'Hello world!';
        $spec = ['foo' => 'bar'];

        $encoded = $ss->encode($data, $spec, false);
        $this->assertNull($encoded);

        $ss->encode($data, $spec, true);
    }

    /**
     * Test decoding a string with an invalid spec.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 400
     */
    public function testUnsupportedSpecDecode() {
        $ss = new SecureString();

        $data = 'Hello world!';
        $spec = ['aes128' => 'bar'];

        $encoded = $ss->encode($data, $spec, false);
        $this->assertNotNull($encoded);

        $badEncoded = $ss->twiddle($encoded, 2, 'foo');
        $decoded = $ss->decode($badEncoded, $spec, false);
        $this->assertNull($decoded);

        $ss->decode($badEncoded, $spec, true);
    }

    /**
     * Test decoding a string with a missing signature.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 403
     */
    public function testMissingSignature() {
        $ss = new SecureString();

        $data = 'Hello world!';
        $spec = ['hsha1' => SecureString::generateRandomKey()];

        $encoded = $ss->encode($data, $spec, false);
        $this->assertNotNull($encoded);

        $badEncoded = $ss->twiddle($encoded, 2, '');
        $decoded = $ss->decode($badEncoded, $spec, false);
        $this->assertNull($decoded);

        $ss->decode($badEncoded, $spec, true);
    }

    /**
     * Test decoding a string with an expired timestamp.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 403
     */
    public function testExpiredTimestamp() {
        $ss = new SecureString();
        $ss->timestampExpiry(-1000);

        $data = 'Hello world!';
        $spec = ['hsha1' => SecureString::generateRandomKey()];

        $encoded = $ss->encode($data, $spec, false);
        $this->assertNotNull($encoded);

        $decoded = $ss->decode($encoded, $spec, false);
        $this->assertNull($decoded);

        $ss->decode($encoded, $spec, true);
    }

    /**
     * Test a string that is only a base64 url encoded hash.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 403
     */
    public function testInsecureString() {
        $ss = new SecureString();
        $str = base64url_encode(json_encode(['foo' => 'bar']));

        $decoded = $ss->decode($str, [], false);
        $this->assertNull($decoded);

        $ss->decode($str, [], true);
    }

    /**
     * Provide a variety of sample data to encode with a {@link SecureCookie}.
     *
     * @return array Returns an array of arrays suitable to pass into tests.
     */
    public function provideSampleData() {
        $result = [
            'int' => [1],
            'timestamp' => [time()],
            'string' => ["Hello world"],
            'unicode' => ['Iñtërnâtiônàlizætiøn'],
            'array' => [[1, 2, 3]],
            'dictionary' => [['uid' => 1234567, 't' => sha1(mt_rand())]],
            'nested' => [['a' => 1234, 'b' => [1, 2, 3]]]
        ];

        return $result;
    }

    public function provideSpecs() {
        $result = [
            'aes128' => [['aes128' => SecureString::generateRandomKey()]],
            'aes256' => [['aes256' => uniqid('pw', true)]],
            'hsha1' => [['hsha1' => uniqid('pw', true)]],
            'hsha256' => [['hsha256' => uniqid('pw', true)]],
            'aes128-hsha1' => [['aes128' => uniqid('pw', true), 'hsha1' => uniqid('pw', true)]],
            'aes256-hsha256' => [['aes256' => uniqid('pw', true), 'hsha256' => uniqid('pw', true)]],
            'double sign' => [['hsha1' => uniqid('pw', true), 'hsha256' => uniqid('pw', true)]]
        ];

        return $result;
    }

    public function provideDataAndSpecs() {
        $data = $this->provideSampleData();
        $specs = $this->provideSpecs();

        $result = [];
        foreach ($data as $dkey => $drow) {
            foreach ($specs as $skey => $srow) {
                $result["$skey: $dkey"] = array_merge($drow, $srow);
            }
        }
        return $result;
    }
}
