<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 15:12
 */

namespace app\tests;


use Dotenv\Dotenv;

class FriendsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Redis
     */
    private $redis;

    protected function setUp() {
        require __DIR__ . '/../../vendor/autoload.php';

        $dotenv = new Dotenv('../../');
        $dotenv->load();

        $this->redis = new \Redis();
        $this->redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }

    protected function tearDown() {
        $this->redis->flushAll();
    }

    private function makeRequest() {
        $handle = curl_init(self::$URL);
        curl_setopt($handle, CURLOPT_COOKIE, self::$COOKIE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($handle);
        curl_close($handle);
    }

    public function testFriendsListNotAvailable() {

    }
}