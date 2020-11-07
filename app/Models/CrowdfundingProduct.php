<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**众筹的业务逻辑
 * 1.需要设置目标金额与截止时间
 * 2.到达戒指时间是如果总订单金额低于目标金额则重抽失败，并退款所有订单
 * 3.到达戒指时间是如果订单金额大等于目标金额则成功
 * 4.众筹订单不支持用户主动申请退款
 * 5.在众筹成功之前订单不能发货
 * Class CrowdfundingProduct
 * @package App\Models
 */
class CrowdfundingProduct extends Model
{
    //定义三种众筹状态
    const STATUS_FUNDING = 'funding';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';

    public static $statusMap = [
        self::STATUS_FAIL => '众筹失败',
        self::STATUS_FUNDING => '众筹中',
        self::STATUS_SUCCESS => '众筹成功'
    ];
    /**
     * @var string[] $fillable 批量操作时可以修改的字段
     */
    protected $fillable = ['total_amount', 'target_amount', 'user_count', 'status', 'end_at'];
    //end_at 会自动转换为Carbon类型
    protected $dates = ['end_at'];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPercentAttribute()
    {
        //已筹金额除以目标金额
        $value = $this->attributes['total_amount'] / $this->attributes['target_amount'];
        //格式化
        return floatval(number_format($value * 100, 2, '.', ''));
    }
}
