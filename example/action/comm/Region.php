<?php

namespace api\action\comm;

use j\api\Base;
use api\Region as Model;

class Region extends Base{
    public function isPublic() {
        return true;
    }

    public function handle($args, $params = []) {
        if(!isset($params['do'])){
            $do = $this->action;
        } else {
            $do = $params['do'];
        }

        $cat = $this->getModel();
        switch($do){
            case 'path' :
                $cid = intval(array_pop($args));
                $data = $cat->getParents($cid, true);
                break;
            case 'name' :
            case 'fullName' :
            case 'child' :
                $cid = intval(array_pop($args));
                $m = 'get' . ucfirst($do);
                $data = call_user_func(array($cat, $m), $cid);
                break;
            default :
                $data = parent::handle($args, $params);
        }

        return $data;
    }

    function getModel(){
        return Model::getInstance();
    }

    public function getDefaultModelMethods(){
        return [
            'getParents',
            'getName',
            'getFullName',
            'getChild',
        ];
    }
}