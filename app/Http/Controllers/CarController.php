<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    // Display a listing of cars
    public function index()
    {
        $cars = Car::all();
        return response()->json($cars);
    }

    // Show the form for creating a new car (if applicable for a web UI)
    public function create()
    {
        // Return view if using Blade templates
        return view('cars.create');
    }

    // Store a newly created car in storage
    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255|unique:cars',
            'year' => 'required|integer',
            'rental_price_per_day' => 'required|numeric',
            'is_available' => 'boolean',
        ]);

        // Create the car
        $car = Car::create($validatedData);

        return response()->json([
            'message' => 'Car created successfully!',
            'car' => $car
        ], 201);
    }

    // Display the specified car
    public function show(Car $car)
    {
        return response()->json($car);
    }

    // Show the form for editing a car (if applicable for a web UI)
    public function edit(Car $car)
    {
        // Return view if using Blade templates
        return view('cars.edit', compact('car'));
    }

    // Update the specified car in storage
    public function update(Request $request, Car $car)
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255|unique:cars,registration_number,' . $car->id,
            'year' => 'required|integer',
            'rental_price_per_day' => 'required|numeric',
            'is_available' => 'boolean',
        ]);

        // Update the car
        $car->update($validatedData);

        return response()->json([
            'message' => 'Car updated successfully!',
            'car' => $car
        ]);
    }

    // Remove the specified car from storage
    public function destroy(Car $car)
    {
        $car->delete();

        return response()->json([
            'message' => 'Car deleted successfully!'
        ]);
    }
}
