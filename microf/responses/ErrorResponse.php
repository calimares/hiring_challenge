<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 14:45
 */

namespace microf\responses;


class ErrorResponse extends JsonResponse
{
    private $errorMessage;

    public function __construct($errorCode, $errorMessage = '')
    {
        parent::__construct();
        $this->errorMessage = $errorMessage;
        $this->setHttpResponseCode($errorCode);
    }

    public function parseResponse()
    {
        $this->viewObjects = array(
            'message' => $this->getErrorMessage(),
            'error' => true
        );
        parent::parseResponse(); 
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

}