# 生成一张日历图片

原项目 [DailyImage](https://github.com/renyijiu/daily_image)，这是 PHP 实现。

## 使用方法
1. 使用 Composer 进行安装

```
composer require dolphin.wang/make-calendar-image
```

2. 按照以下方式引用，可参考 demo

```php
require __DIR__ . '/vendor/autoload.php';

use Dolphin\Wang\Every\Image;

$conf = [
	 'name' => '001.jpg',   // 默认 2018-10-10.jpg
	'today' => '2018-10-10' // 默认 当天
];
// conf 可以不传
$image = new Image($conf);

$image->save();
```

## 体验

下载本项目，在根目录下执行

```
php demo/demo.php
```

即可生成当日图片

## 感谢

1. 图片处理 [PHP Image Manipulation](https://github.com/Intervention/image)

2. 诗词 [一言·古诗词](https://github.com/xenv/gushici)

## 示例

![示例](2018-10-10.jpg)
