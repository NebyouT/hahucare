<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\Manufacturer;

class ManufacturerController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:manufacturers,name',
            'pharma_id' => 'nullable|exists:users,id',
        ]);

        $pharmaId = auth()->user()->user_type === 'pharma'
            ? auth()->id()
            : $request->pharma_id;

        $manufacturer = new Manufacturer();
        $manufacturer->name = $request->name;
        $manufacturer->pharma_id = $pharmaId;
        $manufacturer->save();

        return response()->json([
            'success' => true,
            'message' => __('pharma::messages.manufacturer_created'),
            'manufacturer' => $manufacturer
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('pharma::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('pharma::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }
}
