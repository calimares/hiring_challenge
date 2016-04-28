<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 11:49
 */

namespace microf;


class Request
{
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_GET    = 'GET';
    const METHOD_DELETE = 'DELETE';
   
    /**
     * @var array
     */
    private $postParams;

    /**
     * @var array
     */
    private $getParams;

    /**
     * @var array
     */
    private $putParams;

    /**
     * @var array
     */
    private $deleteParams;

    /**
     * @var string
     */
    private $method;

    /**
     * @var Request
     */
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


    // ---------------------------------------------------------------------------
    /**
     * @param array $post
     * @param array $get
     * @param array $put
     * @param array $delete
     */
    public function __construct($post = array(), $get = array(), $put = array(), $delete = array(), $files = array()) {
        $this->postParams   = $this->setPostParams();
        $this->getParams    = $this->setGetParams();
        $this->putParams    = array();//TODO
        $this->deleteParams = array();//TODO

        $this->method       = strtoupper($_SERVER['REQUEST_METHOD']);
    }

    protected function setPostParams() {
        $this->postParams = $_POST;
    }

    public function getPostParams() {
        return $this->postParams;
    }

    protected function setGetParams() {
        $this->getParams = $_GET;
    }

    public function getGetParams() {
        return $this->getParams;
    }


}