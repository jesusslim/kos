<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 2018/5/16
 * Time: 下午5:57
 */

namespace kos;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionException;

class Container
{

    private static $instance;

    private $classes = [];
    private $instances = [];

    /**
     * @return Container
     */
    public static function getInstance(){
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * @param string $key
     * @param string|Closure|null $class
     */
    public function map($key,$class = null){
        if (is_null($class)) $class = $key;
        $this->classes[$key] = $class;
    }

    /**
     * @param $key
     * @param $instance
     */
    public function mapInstance($key,$instance){
        $this->instances[$key] = $instance;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isMapped($key){
        return isset($this->classes[$key]) || isset($this->instances[$key]);
    }

    /**
     * @param $key
     * @param array $params
     * @return mixed|object
     */
    public function get($key,$params = []){
        if (isset($this->instances[$key])) return $this->instances[$key];
        $concrete = isset($this->classes[$key]) ? $this->classes[$key] : $key;
        return $this->build($concrete,$params);
    }

    /**
     * @param $type
     * @param array $params
     * @return mixed|object
     * @throws ContainerException
     */
    public function build($type,$params = []){
        if ($type instanceof Closure){
            return $this->invoke($type,$params);
        }
        $ref = new ReflectionClass($type);
        if (!$ref->isInstantiable()) throw new ContainerException($type.' is not instantiable');
        $constructor = $ref->getConstructor();
        if (is_null($constructor)) return new $type;
        $params_of_constructor = $constructor->getParameters();
        $params = $this->apply($params_of_constructor,$params);
        try{
            return $ref->newInstanceArgs($params);
        }catch (ReflectionException $exception){
            throw new ContainerException('reflect error:'.$exception->getMessage());
        }
    }

    /**
     * @param $params_need
     * @param array $params
     * @return array
     * @throws ContainerException
     */
    public function apply($params_need,$params = []){
        $result = [];
        foreach ($params_need as $param){
            /* @var \ReflectionParameter $param */
            if (key_exists($param->name,$params)){
                $result[] = $params[$param->name];
            }else{
                $class = $param->getClass();
                $class_name = is_null($class) ? $param->name : $class->name;
                if ($this->isMapped($class_name)){
                    $value = $this->get($class_name);
                }elseif($param->isDefaultValueAvailable()){
                    $value = $param->getDefaultValue();
                }else{
                    throw new ContainerException($param->name.' cant be apply');
                }
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * @param Closure $closure
     * @param array $params
     * @return mixed
     * @throws ContainerException
     */
    public function invoke(Closure $closure,$params = []){
        $rf = new ReflectionFunction($closure);
        $params_of_closure = $rf->getParameters();
        $params = $this->apply($params_of_closure,$params);
        try{
            return $rf->invokeArgs($params);
        }catch (ReflectionException $exception){
            throw new ContainerException('reflect error:'.$exception->getMessage());
        }
    }

    /**
     * @param $class
     * @param $method
     * @param $params
     * @return mixed
     * @throws ContainerException
     */
    public function invokeClass($class,$method,$params = []){
        $rf = new ReflectionMethod($class,$method);
        if (!$rf->isPublic() && !$rf->isStatic()) throw new ContainerException($class.'.'.$method.' is not public or static');
        $params_of_method = $rf->getParameters();
        $obj = $this->get($class,$params);
        $params = $this->apply($params_of_method,$params);
        try{
            return $rf->invokeArgs($obj,$params);
        }catch (ReflectionException $exception){
            throw new ContainerException('reflect error:'.$exception->getMessage());
        }
    }
}