<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Cat;
use Illuminate\Http\Request;

class CatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    public function getUserCats($userId) {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cat $cat) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cat $cat) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cat $cat) {
        //
    }
}
