<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\Request;

class MarkerController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $marker = Marker::create($validatedData);

        return response()->json([
            'message' => 'Marker saved successfully', 
            'marker' => $marker
        ]);
    }

    public function index()
    {
        return Marker::all();
    }

    public function edit(Marker $marker)
    {
        $markers = Marker::all();
        return view('map', compact('marker', 'markers'));
    }
    
    public function update(Request $request, Marker $marker)
    {
        $request->validate([
            'name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
    
        $marker->update($request->all());
        return redirect()->route('marker')->with('success', 'Marker diperbarui!');
    }    

    public function destroy($id)
    {
        $marker = Marker::findOrFail($id);
        $marker->delete();

        return response()->json([
            'message' => 'Marker deleted successfully'
        ]);
    }
}