<?php

namespace api\action;

use j\api\Base;
use j\api\Document as Model;

/**
 * Class Document
 * @package api\action
 */
class Document extends Base{
    public $apiPath = __DIR__;

    function getModel(){
        $model = new Model();
        $model->apiPath = __DIR__;
        return $model;
    }
}