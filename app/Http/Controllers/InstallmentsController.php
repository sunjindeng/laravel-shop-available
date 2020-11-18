<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use function foo\func;

class InstallmentsController extends Controller
{
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', ['installments' => $installments]);
    }


    public function show(Installment $installment)
    {
        $this->authorize('own', $installment);
        //取出当前分期付款的所有的还款计划，并按还款顺序排序
        $items = $installment->items()->orderBy('sequence')->get();
        return view('installments.show', [
            'installment' => $installment,
            'items' => $items,
            //下一个未完成还款的还款计划
            'nextItem' => $items->where('paid_at', null)->first(),
        ]);
    }

    public function payByAlipay(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已关闭');
        }
        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        //获取订单分期付款最近的一个未支付的还款计划
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        //调用支付宝的网页支付
        return app('alipay')->web([
            //支付订单号使用分期流水号+还款计划编号
            'out_trade_no' => $installment->no . '_' . $nextItem->sequence,
            'total_amount' => $nextItem->total,
            'subject' => '支付 laravel——shop 的分期订单' . $installment->no,
            'notify_url' => ngrok_url('installments.alipay.notify'),
            'return_url' => route('installments.alipay.return'),
        ]);
    }

    //支付宝前端回调
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pagers.error', ['msg' => '数据不正确']);
        }
        return view('pages.success', ['msg' => '付款成功']);
    }

    //支付宝后端回调
    public function alipayNotify()
    {
        $data = app('alipay')->verify();
        //如果订单状态不是成功或者结束，则不走后续的逻辑
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }
        //拉起支付是使用的支付订单是由分期号 + 还款计划编号组成的
        list($no, $sequence) = explode('_', $data->out_trade_no);
        //根据分期流水好的查询对应的分期记录
        if (!$installment = Installment::where('no', $no)->first()) {
            return 'fail';
        }
        //根据还款计划编号查询对应的还款计划
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            return 'fail';
        }
        //如果这个还款计划的支付状态是以支付，则告知支付宝此订单已完成，并不在执行后续逻辑
        if ($item->paid_at) {
            return app('alipay')->success();
        }
        \DB::transaction(function () use ($data, $no, $installment, $item) {
            //更新最新的还款计划
            $item->update([
                'paid_at' => Carbon::now(),
                'payment_method' => 'alipay',
                'payment_no' => $data->trade_no,
            ]);
            //如果是第一笔还款
            if ($item->sequence === 0) {
                //将分期付款的状态改为还款中
                $installment->update(['status' => Installment::STATUS_REPAYING]);
                //将分期付款对应的商品订单状态改为以支付
                $installment->order()->update([
                    'paid_at' => Carbon::now(),
                    'payment_method' => 'installment',
                    'payment_no' => $no
                ]);
                //触发商品订单以支付的事件
                event(new OrderPaid($installment->order));
            }
            //如果是最后一笔还款
            if ($item->sequence === $installment->count - 1) {
                //将分期付款状态改为已结清
                $installment->update(['status' => Installment::STATUS_FINISHED]);
            }
        });

        return app('alipay')->success();
    }

    //微信退款回调
    public function wechatRefundNotify(Request $request)
    {
        //给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        //校验微信回调参数
        $data = app('wechat_pay')->verify(null, true);
        //根据单号拆解出对应的商品退款单号及对应的还款计划序号
        list($no, $sequence) = explode('_', $data['out_refund_no']);
        $item = InstallmentItem::query()
            ->whereHas('installment', function ($query) use ($no) {
                $query->whereHas('order', function ($query) use ($no) {
                    $query->where('refund_no', $no);
                });
            })
            ->where('sequence', $sequence)
            ->first();
        //没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if (!$item) {
            return $failXml;
        }
        //如果退款成功
        if ($data['refund_status'] === 'SUCCESS') {
            //将还款计划状态改为退款成功
            $item->update([
                $item->update(['refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS])
            ]);
            $item->installmen->refreshRefundStatus();
        } else {
            //否则将对应还款计划的退款状态改为退款失败
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_FAILED
            ]);
        }
        return app('wechat_pay')->success();
    }
}
