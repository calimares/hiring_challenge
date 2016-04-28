<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 11:39
 */

namespace microf;


use app\domain\chat\FriendsListController;

class Dispatcher
{
    protected static $instance;

    /**
     * Singleton method
     *
     * @return Dispatcher
     */
    public static function getInstance() {
        $className = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $className;
        }
        return self::$instance;
    }


    public function dispatch() {
        try {
            // get request
            $request = Request::getInstance();

            // pre filters
            $response = $this->processPreFilters($request);

            if ($response === true) {
                // get response
                $controller = $this->getController($request);
                $response = $controller->preAction($request);
                if ($response === true) {
                    $response = $controller->action($request);
                }
            }

            $response->render();

        } catch (\Exception $e) {
            die('Unknown error ' . $e->getMessage());
        }
    }

    public function processPreFilters(Request $request) {
        $response = true;
        $allowedDomains = explode(',', getenv('ALLOWED_DOMAINS'));
        $allowBlankReferrer = getenv('ALLOW_BLANK_REFERRER') || false;
        /**
         * Check configuration
         */
        if (empty($allowedDomains) || !is_array($allowedDomains)) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error, invalid domains configuration.']);
            exit();
        }

        /**
         * CORS check
         */
        $httpOrigin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
        if ($allowBlankReferrer || in_array($httpOrigin, $allowedDomains)) {
            header('Access-Control-Allow-Credentials: true');
            if ($httpOrigin) {
                header("Access-Control-Allow-Origin: $httpOrigin");
            }
        } else {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'Not a valid origin.']);
            exit();
        }

        return $response;
    }

    private function getController(Request $request) {
        $path = getenv('MODULE_PATH');
        $endpoint = getenv('ENDPOINT');
        $controller = $endpoint . "Controller";
        $includePath = '/../src/' . $path . '/' . $controller . '.php';
        require_once __DIR__ . $includePath;
        
        $controller = new $controller();

        return $controller;
    }
}