<?php

namespace VaahCms\Modules\Store\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VaahCms\Modules\Store\Models\ProductVariation;
use Illuminate\Support\Facades\Mail;
use WebReinvent\VaahCms\Models\UserBase;

class SendLowCountMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $filtered_data;

    public function __construct($filtered_data)
    {
        //
        $this->filtered_data = $filtered_data;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subject = 'Low Stock Alert';
        $message = '<html><body>';
        $message .= '<p>Hello Everyone, the following items are low in stock:</p>';
        $message .= '<table border="1">';
        $message .= '<tr><th>Product Name</th><th>Variation Name</th></tr>';

        foreach ($this->filtered_data->take(10) as $item) {
            $product_name = isset($item->product) ? $item->product->name : '';
            $variation_name = $item->name ?? '';

            $message .= '<tr>';
            $message .= '<td>' . $product_name . '</td>';
            $message .= '<td>' . $variation_name . '</td>';
            $message .= '</tr>';
        }

        $message .= '</table>';
        $message .= '<p style="margin-top: 0.6rem; font-size: 15px">For more low stock variations, click here</p>';
        $message .= '<p><a href="' . url("/") . '/backend/store#/productvariations?page=1&rows=20&filter[stock_status][]=low_stock"
                     style="background-color: #4CAF50; color: white; padding: 8px 15px; border: none; border-radius: 25px;
                     text-decoration: none; display: inline-block; cursor: pointer; font-size: 14px; width: 7rem;
                     text-align: center; margin-top: 0.3rem;">View</a></p>';
        $message .= '</body></html>';


           UserBase::notifySuperAdmins($subject, $message);


    }
}
