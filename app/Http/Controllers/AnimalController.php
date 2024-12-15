<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnimalController extends Controller
{
    //
    public function index()
    {
        $data = [
            "1" => "cat",
            "2" => "fish",
            "3" => "dog",
            "4" => "bird"
        ];

        return response()->json([
            "status" => "ok",
            "message" => "success",
            "data" => $data
        ], 200);
    }

    public function new(Request $request)
    {
        return $request->animal1;

        // $data = $request->animal1;

        // return response()->json([
        //     "status" => "ok",
        //     "message" => "success",
        //     "data" => $data
        // ], 200);
    }
}
