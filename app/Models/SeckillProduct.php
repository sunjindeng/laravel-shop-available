<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;

class SeckillProduct extends Model
{
    use DefaultDatetimeFormat;
    protected $fillable = ['start_at', 'end_at'];
    protected $dates = ['start_at', 'end_at'];
    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    //定义一个访问器，当前时间早于秒杀开始时间时返回true
    public function getIsBeforeStartAttribute()
    {
        return Carbon::now()->lt($this->start_at);
    }
    //定义访问器，当前时间晚于秒杀结束时间时返回true
    public function getIsAfterEndAttribute()
    {
        return Carbon::now()->gt($this->end_at);
    }
}
