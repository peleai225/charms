<?php

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\OrderRefunded;
use App\Events\StockUpdated;
use App\Listeners\AssignOrderToSuppliers;
use App\Listeners\AwardLoyaltyPointsOnPayment;
use App\Listeners\BroadcastNewOrderNotification;
use App\Listeners\CheckLowStockAlert;
use App\Listeners\CreateAccountingEntryOnPayment;
use App\Listeners\CreateRefundAccountingEntry;
use App\Listeners\DecrementStockOnOrder;
use App\Listeners\IncrementCouponUsage;
use App\Listeners\RestoreStockOnCancel;
use App\Listeners\RestoreStockOnRefund;
use App\Listeners\SendInvoiceOnPayment;
use App\Listeners\SendPushOnOrderUpdate;
use App\Listeners\UpdateCustomerStats;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Événements de commande
        OrderCreated::class => [
            BroadcastNewOrderNotification::class,
            AssignOrderToSuppliers::class,
            SendPushOnOrderUpdate::class,
        ],

        OrderPaid::class => [
            DecrementStockOnOrder::class,
            CreateAccountingEntryOnPayment::class,
            UpdateCustomerStats::class,
            SendInvoiceOnPayment::class,
            IncrementCouponUsage::class,
            AwardLoyaltyPointsOnPayment::class,
            SendPushOnOrderUpdate::class,
        ],

        OrderCancelled::class => [
            RestoreStockOnCancel::class,
            SendPushOnOrderUpdate::class,
        ],

        OrderRefunded::class => [
            CreateRefundAccountingEntry::class,
            RestoreStockOnRefund::class,
        ],

        // Événements de stock
        StockUpdated::class => [
            CheckLowStockAlert::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

