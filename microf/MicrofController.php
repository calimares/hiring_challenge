<?php
/**
 * Created by PhpStorm.
 * User: julian
 * Date: 28/04/16
 * Time: 12:47
 */

namespace microf;


abstract class MicrofController
{
    public abstract function preAction($request);

    public abstract function action($request);

    public abstract function postAction($request);
}