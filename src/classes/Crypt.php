<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use \phpseclib\Crypt\Hash;

use Velocite\Crypt\Binary;
use Velocite\Crypt\Base64UrlSafe;

/**
 * Sodium encryption/decryption code based on HaLite from ParagonIE
 */
class Crypt
{
    /**
     * Crypto default configuration
     *
     * @var array
     */
    protected static $defaults = [];

    /**
     * Defined Crypto instances
     *
     * @var array
     */
    protected static $_instances = [];

    // --------------------------------------------------------------------

    /**
     * Crypto object used to encrypt/decrypt
     *
     * @var object
     */
    protected $crypter;

    /**
     * Hash object used to generate hashes
     *
     * @var object
     */
    protected $hasher;

    /**
     * Crypto configuration
     *
     * @var array
     */
    protected $config = [];

    /**
     * initialisation and auto configuration
     */
    public static function _init() : void
    {
        // load the config
        Config::load('crypt', true);
        static::$defaults = Config::get('crypt', []);

        // keep track of updates to the config
        $update = false;

        // check the sodium config
        if (empty(static::$defaults['sodium']['cipherkey']))
        {
            static::$defaults['sodium'] = ['cipherkey' => sodium_bin2hex(random_bytes(SODIUM_CRYPTO_STREAM_KEYBYTES))];
            $update                     = true;
        }

        // update the config if needed
        if ($update === true)
        {
            try
            {
                Config::save('crypt', static::$defaults);
            }
            catch (FileAccessException $e)
            {
                exit ('Crypt : Failed to write config file');
            }
        }
    }

    /**
     * forge
     *
     * create a new named instance
     *
     * @param string $name   instance name
     * @param array  $config optional runtime configuration
     *
     * @return Crypt
     */
    public static function forge(string $name = '__default__', array $config = []) : Crypt
    {
        if ( ! array_key_exists($name, static::$_instances))
        {
            static::$_instances[$name] = new static($config);
        }

        return static::$_instances[$name];
    }

    /**
     * Return a specific named instance
     *
     * @param string $name instance name
     *
     * @return mixed Crypt if the instance exists, false if not
     */
    public static function instance(string $name = '__default__') : mixed
    {
        if ( ! array_key_exists($name, static::$_instances))
        {
            return static::forge($name);
        }

        return static::$_instances[$name];
    }

    // --------------------------------------------------------------------

    /**
     * Split a key (using HKDF-BLAKE2b instead of HKDF-HMAC-*)
     *
     * @param string $key
     * @param string $salt
     *
     * @return string[]
     */
    protected static function split_keys(string $key, string $salt) : array
    {
        return [
            static::hkdfBlake2b($key, SODIUM_CRYPTO_SECRETBOX_KEYBYTES, 'Halite|EncryptionKey', $salt),
            static::hkdfBlake2b($key, SODIUM_CRYPTO_AUTH_KEYBYTES, 'AuthenticationKeyFor_|Halite', $salt),
        ];
    }

    /**
     * Split a message string into an array (assigned to variables via list()).
     *
     * Should return exactly 6 elements.
     *
     * @param string $ciphertext
     * @param mixed  $message
     *
     * @return array<int, mixed>
     */
    protected static function split_message($message) : array
    {
        // get the message length
        $length = Str::strlen($message, '8bit');

        // check ig it's long enough
        if ($length < 120)
        {
            throw new VelociteException('Crypt: Message is too short');
        }

        // the salt is used for key splitting (via HKDF)
        $salt = Binary::safeSubstr($message, 0, 32);

        // this is the nonce (we authenticated it)
        $nonce = Binary::safeSubstr($message, 32, SODIUM_CRYPTO_STREAM_NONCEBYTES);

        // This is the crypto_stream_xor()ed ciphertext
        $encrypted = Binary::safeSubstr($message, 56, $length - 120);

        // $auth is the last 32 bytes
        $auth = Binary::safeSubstr($message, $length - SODIUM_CRYPTO_GENERICHASH_BYTES_MAX);

        // We don't need this anymore.
        sodium_memzero($message);

        // Now we return the pieces in a specific order:
        return [$salt, $nonce, $encrypted, $auth];
    }

    /**
     * Use a derivative of HKDF to derive multiple keys from one.
     * http://tools.ietf.org/html/rfc5869
     *
     * This is a variant from hash_hkdf() and instead uses BLAKE2b provided by
     * libsodium.
     *
     * Important: instead of a true HKDF (from HMAC) construct, this uses the
     * crypto_generichash() key parameter. This is *probably* okay.
     *
     * @param string $ikm    Initial Keying Material
     * @param int    $length How many bytes?
     * @param string $info   What sort of key are we deriving?
     * @param string $salt
     *
     * @return string
     */
    protected static function hkdfBlake2b(string $ikm, int $length, string $info = '', string $salt = '') : string
    {
        // Sanity-check the desired output length.
        if ($length < 0 or $length > (255 * SODIUM_CRYPTO_GENERICHASH_KEYBYTES))
        {
            throw new VelociteException('hkdfBlake2b Argument 2: Bad HKDF Digest Length');
        }

        // "If [salt] not provided, is set to a string of HashLen zeroes."
        if (empty($salt))
        {
            $salt = str_repeat("\x00", SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
        }

        // HKDF-Extract:
        // PRK = HMAC-Hash(salt, IKM)
        // The salt is the HMAC key.
        $prk = static::raw_keyed_hash($ikm, $salt);

        $t          = '';
        $last_block = '';

        for ($block_index = 1; Str::strlen($t, '8bit') < $length; ++$block_index)
        {
            // T(i) = HMAC-Hash(PRK, T(i-1) | info | 0x??)
            $last_block = static::raw_keyed_hash($last_block . $info . chr($block_index), $prk);

            // T = T(1) | T(2) | T(3) | ... | T(N)
            $t .= $last_block;
        }

        // ORM = first L octets of T
        $orm = Binary::safeSubstr($t, 0, $length);

        return $orm;
    }

    /**
     * Wrapper around SODIUM_CRypto_generichash()
     *
     * Expects a key (binary string).
     * Returns raw binary.
     *
     * @param string $input
     * @param string $key
     * @param int    $length
     *
     * @return string
     */
    protected static function raw_keyed_hash(string $input, string $key, int $length = SODIUM_CRYPTO_GENERICHASH_BYTES) : string
    {
        if ($length < SODIUM_CRYPTO_GENERICHASH_BYTES_MIN)
        {
            throw new VelociteException(sprintf('Output length must be at least %d bytes.', SODIUM_CRYPTO_GENERICHASH_BYTES_MIN));
        }

        if ($length > SODIUM_CRYPTO_GENERICHASH_BYTES_MAX)
        {
            throw new VelociteException(sprintf('Output length must be at most %d bytes.', SODIUM_CRYPTO_GENERICHASH_BYTES_MAX));
        }

        return sodium_crypto_generichash($input, $key, $length);
    }

    /**
     * Calculate a MAC. This is used internally.
     *
     * @param string $message
     * @param string $authKey
     * @param mixed  $auth_key
     *
     * @return string
     */
    protected static function calculate_mac(string $message, $auth_key) : string
    {
        return sodium_crypto_generichash($message, $auth_key, SODIUM_CRYPTO_GENERICHASH_BYTES_MAX);
    }

    /**
     * Verify a Message Authentication Code (MAC) of a message, with a shared
     * key.
     *
     * @param string          $mac      Message Authentication Code
     * @param string          $message  The message to verify
     * @param string          $authKey  Authentication key (symmetric)
     * @param SymmetricConfig $config   Configuration object
     * @param mixed           $auth_key
     *
     * @return bool
     */
    protected static function verify_mac(string $mac, string $message, $auth_key) : bool
    {
        if (Str::strlen($mac, '8bit') !== SODIUM_CRYPTO_GENERICHASH_BYTES_MAX)
        {
            throw new VelociteException('Crypt::verify_mac - Argument 1: Message Authentication Code is not the correct length; is it encoded?');
        }

        $calc = sodium_crypto_generichash($message, $auth_key, SODIUM_CRYPTO_GENERICHASH_BYTES_MAX);
        $res  = Binary::hashEquals($mac, $calc);
        sodium_memzero($calc);

        return $res;
    }

    /**
     * capture static calls to methods
     *
     * @param mixed $method
     * @param array $args   the arguments will passed to $method
     *
     * @return mixed return value of $method
     */
    public static function __callStatic($method, array $args) : mixed
    {
        // static method calls are called on the default instance
        return call_user_func_array([static::instance(), $method], $args);
    }

    /**
     * capture calls to normal methods
     *
     * @param mixed $method
     * @param array $args   the arguments will passed to $method
     *
     * @throws \ErrorException
     *
     * @return mixed return value of $method
     */
    public function __call($method, array $args) : mixed
    {
        // validate the method called
        if ( ! in_array($method, ['encode', 'decode']))
        {
            throw new \ErrorException('Call to undefined method ' . __CLASS__ . '::' . $method . '()', E_ERROR, 0, __FILE__, __LINE__);
        }

        // static method calls are called on the default instance
        return call_user_func_array([$this, $method], $args);
    }

    /**
     * Class constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(static::$defaults, $config);
    }

    /**
     * encrypt a string value, optionally with a custom key
     *
     * @param string      $value     value to encrypt
     * @param string|bool $key       optional custom key to be used for this encryption
     * @param void        $keylength no longer used
     *
     * @return string encrypted value
     */
    protected function encode(string $value, $key = false, $keylength = false) : string
    {
        // get the binary key
        if ( ! $key)
        {
            $key = static::$defaults['sodium']['cipherkey'];
        }
        $key = sodium_hex2bin($key);

        // Generate a nonce and a HKDF salt
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $salt  = random_bytes(32);

        /*
         * Split our key into two keys: One for encryption, the other for
         * authentication. By using separate keys, we can reasonably dismiss
         * likely cross-protocol attacks.
         *
         * This uses salted HKDF to split the keys, which is why we need the
         * salt in the first place.
         */
        list($enc_key, $auth_key) = static::split_keys($key, $salt);

        // Encrypt our message with the encryption key
        $encrypted = sodium_crypto_stream_xor($value, $nonce, $enc_key);
        sodium_memzero($enc_key);

        // Calculate an authentication tag
        $auth = static::calculate_mac($salt . $nonce . $encrypted, $auth_key);
        sodium_memzero($auth_key);

        // total encrypted message
        $message = $salt . $nonce . $encrypted . $auth;

        // wipe every superfluous piece of data from memory
        sodium_memzero($nonce);
        sodium_memzero($salt);
        sodium_memzero($encrypted);
        sodium_memzero($auth);

        // return the base64 encoded message
        return 'S:' . Base64UrlSafe::encode($message);
    }

    /**
     * decrypt a string value, optionally with a custom key
     *
     * @param string      $value     value to decrypt
     * @param string|bool $key       optional custom key to be used for this encryption
     * @param void        $keylength no longer used
     *
     * @access	public
     *
     * @return string encrypted value
     */
    protected function decode(string $value, $key = false, $keylength = false) : string
    {
        $value = explode('S:', $value);

        if ( ! isset($value[1]))
        {
            throw new VelociteException('Encoded string seem to not be a valid sodium encoded string');
        }

        $value = $value[1];

        // get the binary key
        if ( ! $key)
        {
            $key = static::$defaults['sodium']['cipherkey'];
        }
        $key = sodium_hex2bin($key);

        // get the base64 decoded message
        $value = Base64UrlSafe::decode($value);

        // split the message into it's components
        list ($salt, $nonce, $encrypted, $auth) = static::split_message($value);

        /* Split our key into two keys: One for encryption, the other for
         * authentication. By using separate keys, we can reasonably dismiss
         * likely cross-protocol attacks.
         *
         * This uses salted HKDF to split the keys, which is why we need the
         * salt in the first place.
         */
        list($enc_key, $auth_key) = static::split_keys($key, $salt);

        // Check the MAC first
        $res = static::verify_mac($auth, $salt . $nonce . $encrypted, $auth_key);
        sodium_memzero($salt);
        sodium_memzero($auth_key);

        if ($res)
        {
            // crypto_stream_xor() can be used to encrypt and decrypt
            /** @var string $plaintext */
            $message = sodium_crypto_stream_xor($encrypted, $nonce, $enc_key);
        }

        sodium_memzero($encrypted);
        sodium_memzero($nonce);
        sodium_memzero($enc_key);

        return $res ? $message : false;
    }
}
