# hfphp2.0
2.0版，参考ThinkPHP框架（核心板）编写


使用方法更新中、、、



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

三、基础函数

A()

    实例化控制器

D()

    实例化模型

M()

    用于实例化一个没有函数文件的Model

U()

    URL组装，格式：U('[分组/模块/操作]?参数','参数','伪静态后缀')





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

