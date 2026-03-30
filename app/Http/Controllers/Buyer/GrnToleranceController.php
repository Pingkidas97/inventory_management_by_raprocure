<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use App\Models\GrnTolerance;

class GrnToleranceController extends Controller
{
    public function get($buyer_id)
    {
        $buyer_id = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $data = GrnTolerance::where('buyer_id', $buyer_id)->first();
        return response()->json($data);
    }

    public function save(Request $request)
    {
        $buyer_id = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $request->validate([            
            'tolerance' => 'required|integer|min:1|max:99'
        ]);

        $userId = Auth::user()->id;

        $data = GrnTolerance::updateOrCreate(
            ['buyer_id' => $buyer_id],
            [
                'tolerance' => $request->tolerance,
                'updated_by' => $userId,
                'created_by' => $userId
            ]
        );
        return response()->json([
            'status' => true,
            'message' => 'Saved successfully'
        ]);
    }
}