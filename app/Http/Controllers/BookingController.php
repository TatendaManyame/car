<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch all bookings
        $bookings = Booking::with('car', 'user')->get();
        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $validatedData = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Check car availability
        $car = Car::findOrFail($validatedData['car_id']);
        if (!$car->is_available) {
            return response()->json(['message' => 'Car is not available'], 400);
        }

        // Calculate total price
        $days = (new \Carbon\Carbon($validatedData['end_date']))->diffInDays(new \Carbon\Carbon($validatedData['start_date'])) + 1;
        $totalPrice = $days * $car->rental_price_per_day;

        // Create booking
        $booking = Booking::create([
            'user_id' => $request->user()->id, // Assuming Sanctum authentication
            'car_id' => $car->id,
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'total_price' => $totalPrice,
        ]);

        // Update car availability
        $car->update(['is_available' => false]);

        return response()->json(['message' => 'Booking created successfully!', 'booking' => $booking], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch a specific booking
        $booking = Booking::with('car', 'user')->findOrFail($id);
        return response()->json($booking);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate request
        $validatedData = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Find the booking
        $booking = Booking::findOrFail($id);

        // Update booking details
        $booking->update($validatedData);

        return response()->json(['message' => 'Booking updated successfully!', 'booking' => $booking]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the booking
        $booking = Booking::findOrFail($id);

        // Mark the car as available
        $booking->car->update(['is_available' => true]);

        // Delete the booking
        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully!']);
    }
}
