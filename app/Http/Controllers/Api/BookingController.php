<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    // Мои брони
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('tour.destination')
            ->latest()
            ->paginate(10);

        return BookingResource::collection($bookings);
    }

    // Создать бронь
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();

        $booking = DB::transaction(function () use ($request, $data) {
            // Блокируем строку тура, чтобы два человека не заняли одни и те же места
            $tour = Tour::lockForUpdate()->findOrFail($data['tour_id']);

            if (! $tour->is_active) {
                throw ValidationException::withMessages([
                    'tour_id' => ['Этот тур недоступен для бронирования.'],
                ]);
            }

            if ($tour->start_date->isPast()) {
                throw ValidationException::withMessages([
                    'tour_id' => ['Тур уже начался, бронирование закрыто.'],
                ]);
            }

            $available = $tour->availableSeats();

            if ($data['guests'] > $available) {
                throw ValidationException::withMessages([
                    'guests' => ["Недостаточно мест. Свободно: {$available}."],
                ]);
            }

            return Booking::create([
                'user_id'     => $request->user()->id,
                'tour_id'     => $tour->id,
                'guests'      => $data['guests'],
                'total_price' => $tour->price * $data['guests'],
                'status'      => 'pending',
            ]);
        });

        $booking->load('tour.destination');

        return (new BookingResource($booking))
            ->additional(['message' => 'Бронь создана'])
            ->response()
            ->setStatusCode(201);
    }

    // Одна бронь
    public function show(Booking $booking)
    {
        Gate::authorize('view', $booking);

        $booking->load('tour.destination');

        return new BookingResource($booking);
    }

    // Отмена брони
    public function cancel(Booking $booking)
    {
        Gate::authorize('cancel', $booking);

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Бронь уже отменена'], 422);
        }

        if ($booking->tour->start_date->isPast()) {
            return response()->json(['message' => 'Тур уже начался, отмена невозможна'], 422);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load('tour.destination');

        return (new BookingResource($booking))
            ->additional(['message' => 'Бронь отменена']);
    }
}