<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManualOrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendorName;
    public $buyername;
    public $vendorAddress;
    public $items;
    public $order;

    public function __construct($vendorName, $buyername, $vendorAddress, $order, $items)
    {
        $this->vendorName = $vendorName;
        $this->buyername = $buyername;
        $this->vendorAddress = $vendorAddress;
        $this->order = $order;
        $this->items = $items;
    }

    public function build()
    {
        return $this->subject('Manual Order Confirmed (Order No. ' . $this->order['order_number'] . ' )')
                    ->view('buyer.inventory.manual_order_confirmation');
    }

}
