<?php

namespace App\Jobs;

use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

//ShouldQueue代表这是一个异步任务
class RefundInstallmentOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //如果商品订单支付方式不是分期付款，订单未支付，订单退款状态不是退款中，则不执行后面的逻辑
        if ($this->order->payment_method !== 'installment' || !$this->order->paid_at
            || $this->order->refund_status !== Order::REFUND_STATUS_PROCESSING) {
            return;
        }
        //找不到对应的分期付款记录
        if (!$installment = Installment::query()->where('order_id', $this->order->id)->first()) {
            return;
        }
        //遍历分期付款的分期记录
        foreach ($installment->items as $item) {
            //如果还款计划为支付，或者退款状态为退款成功或退款中，则跳过
            if (!$item->paid_at || in_array($item->refund_status, [
                    InstallmentItem::REFUND_STATUS_PROCESSING,
                    InstallmentItem::REFUND_STATUS_SUCCESS
                ])) {
                continue;
            }
            //调用退款逻辑
            try {
                $this->refundInstallmentItem($item);
            }catch (\Exception $exception) {
                \Log::warning('分期退款失败：'. $exception->getMessage(), ['installment_item_id' => $item->id]);
                //如果退款报错了，直接跳过，继续处理下一个
                continue;
            }
        }
        $installment->refreshRefundStatus();
    }

    protected function refundInstallmentItem(InstallmentItem $item)
    {
        //退款单号使用商品的退款号与当前还款计划的序号拼接而成
        $refundNo = $this->order->refund_no . '_' . $item->sequence;
        //根据还款计划的支付方式执行对应的退款逻辑
        switch ($item->payment_method) {
            case 'wechat' :
                app('wechat_pay')->refund([
                    'transaction_id' => $item->payment_no,  //微信订单号
                    'total_fee'       => $item->total * 100, // 原始订单金额，单位分
                    'refund_fee'     => $item->base * 100,  //退款的订单金额，单位分 ，分期付款只退金额
                    'out_refund_no'  => $refundNo,  //退款订单号
                    'notufy_url'     => route('installments.wechat.refund_notify') //todo
                ]);
                //将还款计划退款状态改为退款中
                $item->update([
                    'refund_status' => InstallmentItem::REFUND_STATUS_PROCESSING
                ]);
                break;
            case 'alipay' :
                $ret = app('alipay')->refund([
                    'trade_no'      => $item->payment_no, //支付宝交易号
                    'refund_no'     => $item->base,  //退款金额，单位元，只退本金
                    'out_request_no' => $refundNo   //退款订单号
                ]);
                //根据支付宝的文档，如果返回值里有sub_code 字段说明说明退款失败
                if ($ret->sub_code) {
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_FAILED
                    ]);
                } else {
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS
                    ]);
                }
                break;
            default :
                throw new InvalidRequestException('未知的订单付款方式：'.$item->payment_method);
        }
    }
}
