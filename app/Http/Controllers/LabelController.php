<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labels = Label::all();
        return response()->json($labels);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:labels,name',
        ]);

        $label = Label::create($validated);

        return response()->json($label, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Label $label)
    {
        return response()->json($label);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Label $label)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:labels,name,' . $label->id,
        ]);

        $label->update($validated);
        return response()->json($label);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Label $label)
    {
        $label->delete();
        
        return response()->json(null, 204);
    }
}
