<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 12:47
 */

namespace microf;


use microf\responses\Response;

abstract class MicrofController
{
    /**
     * @param $request
     * @return bool|Response
     */
    public abstract function preAction($request);

    /**
     * @param $request
     * @return Response
     */
    public abstract function action($request);

    /**
     * @param $request
     * @return bool
     */
    public abstract function postAction($request);
}