# hfphp2.0

自主编写的php框架，hfphp2.0版，架构设计主要参考ThinkPHP框架（核心板）编写

所谓框架不在多而在于精，或者说不在于精而在于真正能够内化为自己的东西，所以开始学习TP框架。不仅仅是使用，更是源码的学习。知其然，更要知其所以然。

PHP框架有很多，Yii、laravel更是优秀，但是TP是我使用的第一个框架，有很多缺点，但是仍然有很多优点，仍然有很多我学习的地方。

希望有一起学习TP框架源码的同学可以邮件交流！


框架介绍说明，持续更新中。。。



TODO

 
    1、实现session的mysql驱动
    2、引入各种工具类
        1) 验证码类写完了，但是无法生成图片，应该是框架架构问题，待查。
        
    3、增加日志记录模块，方便bug查询
    4、配置文件分目录、分文件进行配置,方便管理
   
    
    
    
    
目录结构

    www  WEB部署目录（或者子目录）
    ├─index.php       单一入口文件
    ├─README.md       README文件
    ├─App             应用目录
    └─HFPHP           框架目录

    其中框架目录ThinkPHP的结构如下：

    ├─ThinkPHP 框架系统目录（可以部署在非web目录下面）
    │  ├─Common       核心公共函数目录
    │  ├─Conf         核心配置目录
    │  ├─Lang         核心语言包目录
    │  ├─Library      框架类库目录
    │  │  ├─Core      核心Think类库包目录
    │  │  ├─Driver    具体驱动文件目录，例如缓存
    │  │  ├─ ...      更多类库目录
    │  ├─Tpl          系统模板目录
    │  └─HFPHP.php 框架入口文件


自动创建目录

    在第一次访问应用入口文件的时候，会显示如图所示的默认的欢迎页面，并自动生成了一个默认的应用模块Home。

    App
    ├─Common         应用公共模块
    │  └─Conf        应用公共配置文件目录
    ├─Home           默认生成的Home模块
    │  ├─Common      模块函数公共目录
    │  ├─Controller  模块控制器目录
    │  ├─Model       模块模型目录
    │  └─View        模块视图文件目录
    ├─Runtime        运行时目录
    │  ├─Cache       模版缓存目录
    │  ├─Data        数据目录
    │  ├─Compile     编译目录


缓存管理

        支持memcache与redis，默认为memcache。
        
        配置格式
        
        'MEMCACHE_CACHE_CONFIG' =>  array(
            array(
                'host' => '127.0.0.1',
                'port' => '11211'
            )
         ),
        
        'REDIS_CACHE_CONFIG' =>  array(
    
            
            // 'host' => array('192.168.0.1','192.168.0.2')
            'host' => '127.0.0.1',
            'port' => '6379',
            'timeout' => '1',
            'db' => 1
    
        ),
        
        memcache可以配置多台服务器并进行连接，并且在php.ini里面配置，从而存储方式为一致性hash
        redis也可配置多台服务器，但是只会连接一台，建议多台redis服务器的话，配置为主从复制，进行数据备份
        实例化方式如下
        
        $memcache = Cache::getInstance() /$redis = Cache::getInstance('redis');
        
        set/mset默认缓存时长60s,如需修改，直接多传一个时间参数即可。例如$memcache->mset($data,600)/$memcache->set('name','hanfeng',600)

        常见用法如下，除此之外，支持memcache/redis的所有方法.
        
        $data = array(
            'name' => 'lijun',
            'sex' => 'male',
            'class' => 'junior'
        );

        $memcache->mset($data);


        $key = array('name','sex','class');
        
        $memcache->mget($key)

        $memcache->del('name');

        
        
        $memcache->set('num',1);
        
        $memcache->incr('num');
        
        $memcache->decr('num');
        
        $memcache->get('num');
        
        注：如存储的value为数组,先转化为json格式，在进行存储。
        
        
Session与Cookie
        
        注:若将session储存在redis/memcache中，请先配置php.ini文件，之后正常使用session功能即可。
        但是暂时无法存储到数据库，该驱动功能暂时尚未完成。
        
        $session = Session::getInstance();
        
        //设值
        $session->set('key','value');

        //取值
        $session->get('key');
        
        //取所有值
        $session->getAll();

        //删除值
        $session->del('key');

        //删除所有值
        $session->clear();
        
        
        
        $cookie = Cookie::getInstance();
        
        $cookie->set('name','lijun');

        $cookie->get('name');

        $cookie->del('name');

        //清除所有的cookile
        $cookie->clear();

        //设定值时也可以传入配置参数数组，但是删除该值时也必须传入相同的配置参数数组方可删除。
        $config = array('path' => '/','domain' => 'hfphp.com');

        $cookie->set('name','lijun',$config);
        
        //在不符合设定时传入配置的条件下，即不属于该网站下或者路径下，将无法获得值
        $cookie->get('name');

        $cookie->del('name',$config);

        //清除所有的cookile,只是在相同的配置下
        $cookie->clear($config);
        
命名空间
          
          
        支持命名空间，如果不清楚什么是命名空间，可以参考PHP手册相关部分。这里就不对命名空间的用法做过       多的描述了。
                 
                 
常用工具
                 
        验证码
                 
            //生成验证码
            //实例化时可传入配置数组
            $verify = new \Vendor\Verify();
            //生成验证码图片，并且将生成的验证码存在于$_SESSION中 
            //可传入$id字符串，比如'1'，'2',用于区别不同的验证码,默认为空     
            $verify->verify();
            
            //检验验证码
            //传入参数分别为待验证的验证码，以及用于区别不同验证码的id
            $verify->check($code,$id);
            
            
函数说明
       
       
        A()
         
            实例化控制器,文件上方不需引入命名空间，已自动引入
            
            <?php
            
            namespace Home\Controller;
            use Core\Controller;
            
            class IndexController extends Controller {
            
                public function index(){
            
            
                    $user = A('User');
            
                    $user->say();
            
            
                }
         
            
            }
            
            
            
        
        D()
        
            实例化模型
        
        M()
        
            用于实例化一个没有函数文件的Model
        
        U()
        
            URL组装，格式：U('[分组/模块/操作]?参数','参数','伪静态后缀')
        
        
        F()
        
            快速文件数据读取和保存，针对简单类型数据，包括字符串与数组，本方法相当于S()方法的子集
        
       
        
        import()
        
            导入类库方法，可导入本项目下，公共目录下，以及框架自带类库
            
            import('@.Model.AttrModel');   分析路径为  ./App/Home/Model/AttrModel.class.php
            import('Common.test.test');   分析路径为  ./App/Common/test/test.class.php
            import('HF.Core.App');   分析路径为  E:\www\hfphp2.0\HFPHP/Library/Core/App.class.php  
     
            
 
        
一、模型(亦可以使用ORM方式)

select

	1、实现连贯操作,参数只能是字符串
	
	2、直接给select传条件数组


add()

    1、直接给add方法传数组

save()

    1、实现连贯操作，一般前面对一个where()，里面参数为字符串，写修改条件。

    2、直接给save方法传数组，但数组中应有主键，默认为主键为修改条件

delete()

    1、实现连贯操作,参数只能是字符串

    2、直接给delete传条件数组


find()

    用法用select(),只取出一条数据


create()

    用于创建数据，接收post过来的表单

    $User = M("User"); // 实例化User对象

    // 根据表单提交的POST数据创建数据对象

    $User->create();

    $User->add(); // 根据条件保存修改的数据


自动验证

    $validate

    用法与thinkphp相同

二、控制器

assign()

    注入变量

display()

    加载模板





四、模板引擎标签

普通变量标签

    {$tag}

if标签

    {if $tag}

    {else}

    {/if}

foreach标签

    {foreach $tag(key,value)}

       {@key}

       {@value}

    {/foreach}


include标签

    {include file="index.tpl"}


{:function(…)}

    例如，输出U方法的返回值：

    {:U('User/insert')}

    编译后的PHP代码是

    <?php echo U('User/insert');?>

