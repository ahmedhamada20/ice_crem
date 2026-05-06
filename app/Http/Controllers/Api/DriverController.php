<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function __construct(private DeliveryService $service) {}

    public function deliveries(Request $request): JsonResponse
    {
        $deliveries = Delivery::with('order.customer')
            ->where('driver_id', $request->user()->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->orderBy('assigned_at')
            ->get();

        return response()->json(['data' => $deliveries]);
    }

    public function start(Delivery $delivery, Request $request): JsonResponse
    {
        abort_unless($delivery->driver_id === $request->user()->id, 403);

        if ($error = $this->guardFutureDelivery($delivery)) return $error;

        $this->service->startDelivery($delivery, $request->lat, $request->lng);
        return response()->json(['success' => true]);
    }

    public function complete(Delivery $delivery, Request $request): JsonResponse
    {
        abort_unless($delivery->driver_id === $request->user()->id, 403);

        if ($error = $this->guardFutureDelivery($delivery)) return $error;

        $request->validate([
            'lat'       => 'nullable|numeric',
            'lng'       => 'nullable|numeric',
            'signature' => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $sigPath = null;
        if ($request->filled('signature') && str_starts_with($request->signature, 'data:image')) {
            $img = base64_decode(explode(',', $request->signature)[1] ?? '');
            $sigPath = 'signatures/'.uniqid('sig_').'.png';
            Storage::disk('public')->put($sigPath, $img);
        }

        $this->service->completeDelivery($delivery, [
            'lat'       => $request->lat,
            'lng'       => $request->lng,
            'signature' => $sigPath,
            'notes'     => $request->notes,
        ]);

        return response()->json(['success' => true]);
    }

    public function fail(Delivery $delivery, Request $request): JsonResponse
    {
        abort_unless($delivery->driver_id === $request->user()->id, 403);

        if ($error = $this->guardFutureDelivery($delivery)) return $error;

        $request->validate(['reason' => 'required|string']);
        $this->service->failDelivery($delivery, $request->reason);
        return response()->json(['success' => true]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        Cache::put("driver_loc_{$request->user()->id}", [
            'lat' => $request->lat,
            'lng' => $request->lng,
            'at'  => now()->toIso8601String(),
        ], 600);

        return response()->json(['success' => true]);
    }

    /**
     * Refuse to act on a delivery whose order's delivery_date is in the future.
     * Returns null when the date is OK; an error JsonResponse otherwise.
     */
    private function guardFutureDelivery(Delivery $delivery): ?JsonResponse
    {
        $deliveryDate = $delivery->order?->delivery_date;
        if ($deliveryDate && $deliveryDate->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تنفيذ هذه التوصيلة قبل موعدها (' . $deliveryDate->format('d/m/Y') . ')',
            ], 422);
        }
        return null;
    }
}
