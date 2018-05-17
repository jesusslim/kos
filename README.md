# kos
php concise container/di

## usage

### Install

kos in packagist:[https://packagist.org/packages/jesusslim/kos](https://packagist.org/packages/jesusslim/kos)

Install:

	composer require jesusslim/kos

### Methods

#### getInstance()

get instance of kos\Container.

#### map($key,$class = null)

map a class or closure to container.

    Container::getInstance()->map(Foo::class);
    Container::getInstance()->map('foo',Foo::class);
    Container::getInstance()->map('bar',function($foo,$bar){return $foo + $bar;});
    
#### mapInstance($key,$instance)

map an instance to container.

    Container::getInstance()->mapInstance('foo',new Foo());
    
#### isMapped($key)

#### get($key,$params = [])

if $key is mapped in container as an instance,return the instance;

if $key is mapped in container as a class,return the an instance of the class;

if $key is mapped in container as a closure,return the result of the closure;

if $key is not mapped in container,reflect $key and return the instance/result;

the values in $params can be used as the params of the constructor of class or the params of closure;

    class Foo{
    
        private $bar;
    
        public function __construct($bar)
        {
            $this->bar = $bar;
        }
    
        public function getBar(){
            return $this->bar;
        }
    }
    $container = Container::getInstance();
    
    $container->map(Foo::class);
    $container->mapInstance('bar',677);
    $container->map('sum',function(Foo $foo,$inc){
        return $foo->getBar()+$inc;
    });
    
    $foo = $container->get(Foo::class);
    /* @var Foo $foo */
    echo $foo->getBar();
    //result is 677
    
    $foo = $container->get(Foo::class,['bar' => 688]);
    echo $foo->getBar();
    //result is 688;
    
    echo $container->get('sum',['inc' => 100]);
    //result is 777
    
#### invoke(Closure $closure,$params = [])

invoke a closure , the params of the closure will be filled by the container.
 
    echo $container->invoke(function(Foo $foo,$inc){
        return $foo->getBar()+$inc;
    },['inc' => 100]);
    //result is 777
    
#### invokeClass($class,$method,$params = [])

invoke a class->method , the params of the closure will be filled by the container.

    echo $container->invokeClass(Foo::class,'getBar',[]);
    //result is 677
    
## others

kos is a concise container/DI lib for simple use.

and [pinject](https://github.com/jesusslim/pinject) is  a lib for the complex use,like caching instances,ArrayAccess implements and chaining operations/workflow with container.
