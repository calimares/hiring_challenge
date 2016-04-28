<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 15:12
 */

namespace app\tests;


use app\domain\chat\FriendsList;
use Dotenv\Dotenv;

class FriendsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Redis
     */
    private $redis;

    static $URL = "localhost:8080/chat/FriendsList";
    static $COOKIE = "app=hash";

    protected function setUp() {
        require __DIR__ . '/../../vendor/autoload.php';

        $dotenv = new Dotenv('../../');
        $dotenv->load();

        $this->redis = new \Redis();
        $this->redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $this->redis->flushAll();
    }

    protected function tearDown() {
        $this->redis->flushAll();
    }

    private function saveSampleData() {
        $this->redis->set('PHPREDIS_SESSION:hash', ['default' => ['id' => 1]]);
        $this->redis->set('chat:online:176733', true);
        $this->redis->set('chat:friends:1', new FriendsList([
            [
                'id' => 1,
                'name' => 'Project 1',
                'threads' => [
                    [
                        'online' => false,
                        'other_party' => [
                            'user_id' => 176733,
                        ]
                    ]
                ]
            ],
            [
                'id' => 2,
                'name' => 'Project 2',
                'threads' => [
                    [
                        'online' => false,
                        'other_party' => [
                            'user_id' => 176733,
                        ]
                    ]
                ]
            ]
        ]));
    }

    /**
     * Función auxiliar para hacer un pedido por curl al microservicio FriendsList
     * @return mixed
     */
    private function makeRequest() {
        $handle = curl_init(self::$URL);
        curl_setopt($handle, CURLOPT_COOKIE, self::$COOKIE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($handle);
        curl_close($handle);

        return json_decode($response, true);
    }

    public function testFriendsListNotAvailable() {
        $response = $this->makeRequest();
        $expectedJson = '{"error":true,"message":"Friends list not available."}';
        $expectedArray = json_decode($expectedJson, true);

        $this->assertEquals($expectedArray, $response);
    }

    public function testSampleFriendsList() {
        $this->saveSampleData();
        $response = $this->makeRequest();
        $expectedJson = '[{"id":1,"name":"Project 1","threads":[{"online":true,"other_party":{"user_id":176733}}]},{"id":2,"name":"Project 2","threads":[{"online":true,"other_party":{"user_id":176733}}]}]';
        $expectedArray = json_decode($expectedJson, true);

        $this->assertEquals($expectedArray, $response);
    }

    

}