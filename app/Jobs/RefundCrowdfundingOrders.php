<?php

namespace App\Jobs;

use App\Models\CrowdfundingProduct;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class RefundCrowdfundingOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crowdfunding;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CrowdfundingProduct $crowdfundingProduct)
    {
        $this->crowdfunding = $crowdfundingProduct;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //如果众筹的状态不是失败则不执行退款，原则上不会发生，这是只是增加健壮性
        if ($this->crowdfunding->status !== CrowdfundingProduct::STATUS_FAIL) {
            return;
        }
        //将定时任务中的众筹失败瑞款代码移到这里
        $orderService = app(OrderService::class);
        Order::query()
            ->where('type', Order::TYPE_CROWDFUNDING)
            ->whereNotNull('paid_at')
            ->whereHas('item', function ($query) {
                $query->where('product_id', $this->crowdfunding->product_id);
            })
            ->get()
            ->each(function (Order $order) use ($orderService) {
                $orderService->refundOrder($order);
            });
    }
}
