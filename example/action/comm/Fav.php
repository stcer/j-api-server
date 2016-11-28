<?php

namespace api\action\comm;

use j\api\Base;
use api\action\Exception;

/**
 * Class Fav
 * @package api\action\comm
 */
class Fav extends Base{

    /**
     * add photo to my fav
     * @param int $photoId
     * @return string
     * @throws Exception
     */
    function addPhoto($photoId){
        return "success";
    }
}