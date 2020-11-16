<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_REPAYING = 'repaying';
    const STATUS_FINISHED = 'finished';

    public static $statusMap = [
        self::STATUS_PENDING => '未执行',
        self::STATUS_REPAYING => '还款中',
        self::STATUS_FINISHED => '已完成'
    ];

    protected $fillable = ['no', 'total_amount', 'count', 'fee_rate', 'fine_rate', 'status'];
    protected static function boot()
    {
        parent::boot();
        //监听模型创建事件，在写入数据库之前出发
        static::creating(function ($model) {
            //如果模型的 no 字段为空
            if (!$model->no) {
                //调用findAbailableNo 生成分期流水号
                $model->no = static::findAvailableNo();
                //如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->hasMany(InstallmentItem::class);
    }

    public static function findAvailableNo()
    {
        //分期流水号前缀（年月日添加六位随机数）
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            //随机生成 6 位的数字
            // random_int (php7添加的随机数，来替代mt_rand,随机数包含两个参数)str_pad 把字符串填充为心的长度
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            //判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('find installment no faild');
        return false;
    }



}
