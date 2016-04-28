<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 11:49
 */

namespace microf\responses;


abstract class Response
{
    /**
     * @var array
     */
    protected $viewObjects;

    /**
     * @var int
     */
    protected $responseCode;

    /**
     * @var array
     */
    protected $headers;

    // -------------------------------------------------------------------------
    public function __construct() {
        $this->viewObjects  = array();
        $this->headers      = array();
        $this->responseCode = 200;
    }

    // -------------------------------------------------------------------------
    /**
     * @param int $httpErrorCode
     */
    public function setHttpResponseCode($httpErrorCode) {
        if(!is_numeric($httpErrorCode)) {
            throw new Exception("Invalid http response code [$httpErrorCode]");
        }
        $this->responseCode = $httpErrorCode;
    }
    // -------------------------------------------------------------------------
    /**
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value) {
        $this->headers[$name] = $value;
    }

    // -------------------------------------------------------------------------
    /**
     * @return array
     */
    public function getObjects(){
        return $this->viewObjects;
    }

    // -------------------------------------------------------------------------
    /**
     * @return boolean
     */
    protected function existsError() {
        /*
         * Los codigos http 1xx y 2xx no son de error
         * http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        return ($this->getHttpResponseCode() > 299);
    }

    // -------------------------------------------------------------------------
    /**
     * @return int
     */
    public function getHttpResponseCode() {
        return $this->responseCode;
    }
    // -------------------------------------------------------------------------
    /**
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    // -------------------------------------------------------------------------
    public function render() {

        if ($this->getHttpResponseCode()) {
            header('HTTP/1.0', true, $this->getHttpResponseCode());
        } else {
            header('HTTP/1.0', true, 200);
        }

        foreach($this->getHeaders() as $headerName => $headerValue) {
            if (!empty($headerName) && !empty($headerValue)) {
                header("$headerName: $headerValue");
            }
        }

        $this->parseResponse();

    }

    abstract protected function parseResponse();

}