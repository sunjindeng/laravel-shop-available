<?php

namespace App\Console\Commands\Cron;

use App\Models\Installment;
use App\Models\InstallmentItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateInstallmentFine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:calculate-installment-fine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算分期付款逾期费';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        InstallmentItem::query()
            ->with(['installment'])
            ->whereHas('installment', function ($query) {
                //对应分期状态为还款中
                $query->where('status', Installment::STATUS_REPAYING);
            })
            //截止日期在当前时间之前
            ->where('due_date', '<=', Carbon::now())
            //尚未还款
            ->whereNull('paid_at')
            //chunkById 避免一次性差太多
            ->chunkById(1000, function ($items) {
                //遍历查询出来的还款计划
                foreach ($items as $item) {
                    //通过Carbon对象的diffInDays直接得到逾期天数
                    $overdueDays = Carbon::now()->diffInDays($item->due_date);
                    //本金与手续费只和
                    $bash = big_number($item->base)->add($item->fee)->getValue();
                    //计算逾期费
                    $fine = big_number($bash)
                        ->multiply($overdueDays)
                        ->multiply($item->installment->fine_rate)
                        ->divide(100)
                        ->getValue();
                    //避免逾期费高于本金与手续费之和，使用compareTo方法来判断
                    //如果$fine大于$base,则compareTo 会返回1，相等返回0，小雨返回-1
                    $fine = big_number($fine)->compareTo($bash) === 1 ? $bash : $fine;
                    $item->update(['fine' => $fine]);
                }
            });

    }











}
