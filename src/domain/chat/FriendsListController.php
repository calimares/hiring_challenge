<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 12:41
 */

namespace app\domain\chat;

/**
 * Some constants
 */
define('FRIENDS_CACHE_PREFIX_KEY', 'chat:friends:{:userId}');
define('ONLINE_CACHE_PREFIX_KEY', 'chat:online:{:userId}');

use microf\responses\ErrorResponse;
use microf\responses\JsonResponse;
use microf\MicrofController;

class FriendsListController extends MicrofController
{
    private $redis;

    private function setRedis() {
        $redisHost = getenv('REDIS_HOST');
        $redisPort = getenv('REDIS_PORT');
        /**
         * Check configuration
         */
        if (empty($redisHost) || empty($redisPort)) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error, invalid redis configuration.']);
            exit();
        }

        // Create a new Redis connection
        $redis = new \Redis();
        $redis->connect($redisHost, $redisPort);

        if (!$redis->isConnected()) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error, can\'t connect.']);
            exit();
        }

        // Set Redis serialization strategy
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        $this->redis = $redis;
    }

    public function preAction($request)
    {
        $response = true;
        $this->setRedis();

        /**
         * No cookie, no session ID.
         */
        if (empty($_COOKIE['app'])) {
            $response = new ErrorResponse(403, 'Not a valid session.');
        }

        return $response;
    }

    public function action($request)
    {
        try {
            $response = new JsonResponse();
            $sessionHash = $_COOKIE['app'];
            $session = $this->redis->get(join(':', ['PHPREDIS_SESSION', $sessionHash]));

            // Don't set cookie, let's keep it lean
            header_remove('Set-Cookie');

            if (!empty($session['default']['id'])) {
                $friendsList = $this->redis->get(str_replace('{:userId}', $session['default']['id'], FRIENDS_CACHE_PREFIX_KEY));
                if (!$friendsList) {
                    // No friends list yet.
                    http_response_code(200);
                    echo json_encode([]);
                    exit();
                }
            } else {
                $response = new ErrorResponse(404, 'Friends list not available.');
            }

            $friendUserIds = $friendsList->getUserIds();

            if (!empty($friendUserIds)) {
                $keys = array_map(function ($userId) {
                    return str_replace('{:userId}', $userId, ONLINE_CACHE_PREFIX_KEY);
                }, $friendUserIds);

                // multi-get for faster operations
                $result = $this->redis->mget($keys);

                $onlineUsers = array_filter(
                    array_combine(
                        $friendUserIds,
                        $result
                    )
                );

                if ($onlineUsers) {
                    $friendsList->setOnline($onlineUsers);
                }
            }
            $response->setDataArray($friendsList->toArray());

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Unknown exception. ' . $e->getMessage()]);
            exit();
        }

        return $response;
    }

    public function postAction($request)
    {
        // TODO: Implement postAction() method.
    }
}