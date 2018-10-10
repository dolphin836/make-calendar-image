<?php

namespace Dolphin\Wang\Every;

use Intervention\Image\ImageManagerStatic as ImageManager;
use Overtrue\ChineseCalendar\Calendar;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

/**
 * 生成一张每日图片
 * 
 * @author Dolphin Wang <wanghaibing@shein.com>
 * @license BSD-3-Clause
 * @version 1.0.0
 * @link https://blog.haibing.site
 */

class Image
{
    /**
     * 背景颜色
     * 
     * @var string
     */
    private $bg_color   = '#FFFFFF';
    
    /**
     * 进度条已过去部分的颜色
     * 
     * @var string
     */
    private $pass_color = '#6391A9';
    
    /**
     * 进度条剩余部分的颜色
     * 
     * @var string
     */    
    private $have_color = '#C9CDD8';

    /**
     * 一年中的第几天
     * 
     * @var int
     */    
    private $day;

    /**
     * 年
     * 
     * @var int
     */    
    private $y;

    /**
     * 月
     * 
     * @var int
     */    
    private $m;

    /**
     * 日
     * 
     * @var int
     */    
    private $d;

    /**
     * 星期几
     * 
     * @var string
     */    
    private $w;

    /**
     * 百分比，两位小数
     * 
     * @var float
     */    
    private $per;

    /**
     * 进度条总长度
     * 
     * @var int
     */    
    private $len = 500;

    /**
     * 已过去的长度
     * 
     * @var int
     */    
    private $pass_len;

    /**
     * 农历字符串
     * 
     * @var string
     */    
    private $non_text;

    /**
     * 进度字符串
     * 
     * @var string
     */    
    private $per_text;

    /**
     * 诗词默认数据
     * 
     * @var array
     */ 
    private $fine = [
        'title' => '终南别业',
      'content' => '行到水穷处，坐看云起时。',
       'author' => '王维'
    ];

    /**
     * 诗词 API URL
     * 
     * @var string
     */
    private $server = 'https://v2.jinrishici.com/one.json';

    /**
     * Http Client
     * 
     * @var object
     */
    private $guzzle;

    /**
     * 日历
     * 
     * @var object
     */
    private $calendar;

    /**
     * 图片
     * 
     * @var object
     */
    private $img;

    /**
     * 字体
     * 
     * @var string
     */
    private $font_path = __DIR__ . '/San.ttf';

    /**
     * 文件名
     * 
     * @var string
     */
    private $name;

    /**
     * 那一天
     * 
     * @var string
     */
    private $today;

    public function __construct($conf = [])
    {
        $this->today    = isset($conf['today']) && $conf['today'] !== '' ? strtotime($conf['today']) : time();
        $this->name     = isset($conf['name'])  && $conf['name']  !== '' ? $conf['name']             : date("Y-m-d", $this->today) . '.jpg';

        $this->guzzle   = new Client();

        $this->calendar = new Calendar();

        $this->img      = ImageManager::canvas(600, 800, $this->bg_color);
    }

    public function save()
    {
        // 初始化
        $this->init();

        // 开始绘制

        // 边框
        $this->frame();

        // 文字
        $this->text();

        // 进度条
        $this->progress();

        // 诗词
        $this->fine();

        // 生成图片
        $this->img->save($this->name);
    }

    private function init()
    {
        // 一年中的第几天
        $this->day      = date("z", $this->today);
        // 年
        $this->y        = date("Y", $this->today);
        // 月
        $this->m        = date("n", $this->today);
        // 日
        $this->d        = date("j", $this->today);
        // 百分比，两位小数
        $this->per      = round(($this->day / 365) * 100, 2);
        // 已过去的长度
        $this->pass_len = ceil($this->len * ($this->per / 100));

        // 日历
        $cale           = $this->calendar->solar($this->y, $this->m, $this->d);

        $this->w        = $cale['week_name'];

        // 拼接展示字符串
        $this->non_text = $cale['ganzhi_year'] . '年 ' . $cale['lunar_month_chinese'] . $cale['lunar_day_chinese'];

        $this->per_text = '第 ' . $this->day . ' 天，进度已消耗 ' . $this->per . '%';

        try {
            $result = $this->guzzle->request('GET', $this->server, [
                'verify' => false
            ]);
        } catch(RequestException $e) {
            var_dump('请求诗词接口异常.');
        }
        
        if ($result->getStatusCode() === 200) {
            $content = json_decode($result->getBody()->getContents(), true);
        
            if ($content['status'] === 'success') {
                $data = $content['data'];
        
                $this->fine = [
                    'title' => $data['origin']['title'],
                  'content' => $data['content'],
                   'author' => $data['origin']['author']
                ];
            }
        }
    }

    /**
     * 绘制边框
     */
    private function frame()
    {
        $pass_color = $this->pass_color;
        $bg_color   = $this->bg_color;

        $this->img->rectangle(16, 16, 585, 785, function ($draw) use ($bg_color, $pass_color) {
            $draw->background($bg_color);
            $draw->border(5, $pass_color);
        });

        $this->img->rectangle(26, 26, 575, 775, function ($draw) use ($bg_color, $pass_color) {
            $draw->background($bg_color);
            $draw->border(2, $pass_color);
        });

        $this->img->rectangle(51, 461, 550, 750, function ($draw) use ($bg_color, $pass_color) {
            $draw->background($bg_color);
            $draw->border(1, $pass_color);
        });
    }

    /**
     * 绘制诗词
     */
    private function fine()
    {
        $pass_color = $this->pass_color;
        $font_path  = $this->font_path;

        $this->img->text($this->fine['title'], 300, 520, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(18);
            $font->color($pass_color);
            $font->align('center');
            $font->valign('center');
        });

        $this->img->text($this->fine['content'], 300, 596, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(20);
            $font->color($pass_color);
            $font->align('center');
            $font->valign('center');
        });

        $this->img->text('--- ' . $this->fine['author'], 540, 672, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(18);
            $font->color($pass_color);
            $font->align('right');
            $font->valign('center');
        });
    }

    /**
     * 绘制进度条
     */
    private function progress()
    {
        $pass_color = $this->pass_color;
        $have_color = $this->have_color;

        // 已过去的
        $this->img->rectangle(51, 401, 51 + $this->pass_len, 430, function ($draw) use ($pass_color) {
            $draw->background($pass_color);
        });

        // 剩余的
        $this->img->rectangle(51 + $this->pass_len, 401, 550, 430, function ($draw) use ($have_color) {
            $draw->background($have_color);
        });
    }

    /**
     * 绘制文字
     */
    private function text()
    {
        $pass_color = $this->pass_color;
        $font_path  = $this->font_path;
        // 日期

        $this->img->text(date("Y.m.d", $this->today), 61, 81, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(24);
            $font->color($pass_color);
        });

        // 星期几

        $this->img->text($this->w, 540, 81, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(24);
            $font->color($pass_color);
            $font->align('right');
        });

        $this->img->text($this->d, 300, 200, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(150);
            $font->color($pass_color);
            $font->align('center');
            $font->valign('center');
        });

        $this->img->text($this->non_text, 300, 320, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(20);
            $font->color($pass_color);
            $font->align('center');
            $font->valign('center');
        });

        $this->img->text($this->per_text, 300, 360, function($font) use ($font_path, $pass_color) {
            $font->file($font_path);
            $font->size(18);
            $font->color($pass_color);
            $font->align('center');
            $font->valign('center');
        });

    }
}
