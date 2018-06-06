<?php

namespace j\api;

/**
 * Class Base
 * @package j\api
 */
class Base{

    /**
     * @var
     */
    protected $action;


    public function bootstrap($params = []){

    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function yar() {
        return $this->handle(func_get_args());
    }

    /**
     * @param $args
     * @param $request
     * @return mixed
     * @throws Exception
     */
    public function handle($args, $request = []){
        if($this->action){
            $method = str_replace('.', '_', $this->action);
            if($this->action != 'handle' && method_exists($this, $method)){
                return call_user_func_array(array($this, $method), $args);
            }

            if(method_exists($this, 'getModel')){
                $model = $this->getModel();

                if(!method_exists($this, 'getDefaultModelMethods')
                    || in_array($method, $this->getDefaultModelMethods())
                ){
                    if(method_exists($model, $method)){
                        return call_user_func_array(array($model, $method), $args);
                    }
                }
            }
        } else if(method_exists($this, 'execute')){
            return call_user_func(array($this, 'execute'), $args);
        }

        throw new Exception("Method not found");
    }

    /**
     * @param mixed $action
     */
    public function setAction($action) {
        $this->action = $action;
    }
}
