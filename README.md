# draw
抽奖小程序


###composer create-project laravel/laravel=5.6.* 
- php artisan key:generate
- php artisan storage:link

### composer require encore/laravel-admin
- php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
- php artisan admin:install

### composer require "overtrue/laravel-lang:~3.0"
- php artisan lang:publish zh-CN

### composer require stevenyangecho/laravel-u-editor
- php artisan vendor:publish

### composer require fruitcake/laravel-cors -vvv
- php artisan vendor:publish
- 在config/cors.php配置文件中，修改paths配置如下：'paths'  => ['api/*'],
- 在app/Http/Kernel.php文件 protected $middleware 位置加入\Fruitcake\Cors\HandleCors::class,

### composer require spatie/laravel-activitylog 1.3.1
- php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="migrations"
- php artisan migrate 
- php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="config"

