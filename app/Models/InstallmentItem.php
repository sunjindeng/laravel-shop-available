<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Moontoast\Math\BigNumber;

class InstallmentItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    public static $refundStatusMap = [
        self::REFUND_STATUS_SUCCESS => '退款成功',
        self::REFUND_STATUS_FAILED => '退款失败',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_PENDING => '未退款'
    ];

    protected $fillable = [
        'sequence',
        'base',
        'fee',
        'fine',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status'
    ];

    protected $dates = ['due_date'];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function getTotalAttribute()
    {
        //小数点计算需要用bcmath扩展提供的函数
       // $total = bcadd($this->base, $this->fee, 2);
        //mootoast/math 库主要提供了\Moontoast\Math\BigNumber这个类，这个类的构造函数接受了两个参数。第一个参数就是我们要参与运算的数值，第二个参数是
        //可选参数，用于表示我们希望的计算精度（即小数点后几位）提供了常见的运算方法，add（），subtract()减法，multiply（）乘法，divide（）除法，等等
        //big_number 在helpers.php中以封装
        $total = big_number($this->base, 2)->add($this->fee);
        if(!is_null($this->fine)) {
            $total->add($this->fine);
        }
        return $total->getValue();
    }

    //当前还款计划是否已经逾期访问器
    public function getIsOverdueAttribute()
    {
        return Carbon::now()->gt($this->due_date);
    }



}
