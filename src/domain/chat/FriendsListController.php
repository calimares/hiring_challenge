<?php
/**
 * Controller para encapsular la lógica para obtener la lista de amigos
 * Date: 28/04/16
 */

//namespace app\domain\chat;

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
            throw new \Exception('Server error, invalid redis configuration.');
        } else {
            // Create a new Redis connection
            $redis = new \Redis();
            $redis->connect($redisHost, $redisPort);

            if (!$redis->isConnected()) {
                throw new \Exception('Server error, can\'t connect.');
            } else {
                // Set Redis serialization strategy
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $this->redis = $redis;
            }
        }
    }

    public function preAction($request)
    {
        $response = true;

        try {
            $this->setRedis();
            /**
             * No cookie, no session ID.
             */
            if (empty($_COOKIE['app'])) {
                $response = new ErrorResponse(403, 'Not a valid session.');
            }
        } catch (\Exception $e) {
            $response = new ErrorResponse(500, $e->getMessage());
        }

        return $response;
    }

    /**
     * @param $request
     * @return ErrorResponse|JsonResponse
     */
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
                    return $response;
                }
            } else {
                $response = new ErrorResponse(404, 'Friends list not available.');
                return $response;
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
            $response = new ErrorResponse(500, 'Unknown exception. ' . $e->getMessage());
        }

        return $response;
    }

    public function postAction($request)
    {
        // TODO: Implement postAction() method.
    }
}