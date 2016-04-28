<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 14:21
 */

namespace microf\responses;


class JsonResponse extends Response
{
    public function __construct(array $viewArray = array()) {
        parent::__construct();
        header('Content-Type: application/json; charset=utf-8');

        $this->viewObjects = $viewArray;
    }

    // -------------------------------------------------------------------------
    /**
     * @return string
     */
    protected function parseResponse() {
        echo json_encode($this->viewObjects);
    }

    public function setDataArray($array) {
        $this->viewObjects = $array;
    }
}