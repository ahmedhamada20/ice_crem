<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    public function __construct(private OrderService $orderService) {}

    public function assignDriver(Order $order, int $driverId, ?string $vehicle = null): Delivery
    {
        $order->update(['status' => 'delivering']);

        return Delivery::updateOrCreate(
            ['order_id' => $order->id],
            [
                'driver_id'      => $driverId,
                'vehicle_number' => $vehicle,
                'assigned_at'    => now(),
                'status'         => 'assigned',
            ]
        );
    }

    public function startDelivery(Delivery $delivery, ?float $lat = null, ?float $lng = null): Delivery
    {
        $delivery->update([
            'status'     => 'in_progress',
            'started_at' => now(),
            'start_lat'  => $lat,
            'start_lng'  => $lng,
        ]);

        return $delivery;
    }

    /**
     * Driver completes a delivery.
     * Wraps the operation in a transaction so the delivery + the related
     * order's stock deduction happen together.
     */
    public function completeDelivery(Delivery $delivery, array $data): Delivery
    {
        return DB::transaction(function () use ($delivery, $data) {
            $delivery->update([
                'status'        => 'delivered',
                'delivered_at'  => now(),
                'end_lat'       => $data['lat'] ?? null,
                'end_lng'       => $data['lng'] ?? null,
                'signature'     => $data['signature'] ?? null,
                'photo'         => $data['photo'] ?? null,
                'notes'         => $data['notes'] ?? null,
            ]);

            // Mark the order delivered + deduct stock (idempotent)
            if ($delivery->order) {
                $delivery->order->loadMissing('items');
                $this->orderService->markDelivered($delivery->order);
            }

            return $delivery->fresh();
        });
    }

    public function failDelivery(Delivery $delivery, string $reason): Delivery
    {
        $delivery->update([
            'status'         => 'failed',
            'failure_reason' => $reason,
        ]);

        return $delivery;
    }
}
