<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class CommonController extends Controller
{
    public function getStateByCountryId(Request $request)
    {
        $country_id = $request->country_id;
        if(empty($country_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }

        $states = DB::table("states")
                            ->select("id", "name")
                            ->where("country_id", $country_id)
                            ->orderBy("name", "ASC")
                            ->pluck("name", "id")->toArray();
        $html = '';
        foreach ($states as $id => $name) {
            $html.= '<option value="' . $id . '">' . $name . '</option>';
        }
        return response()->json([
            'status' => true,
            'message' => 'State List',
            'state_list' => $html
        ]);
    }
    public function getCityByStateId(Request $request)
    {
        $state_id = $request->state_id;
        if(empty($state_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }

        $cities = DB::table("cities")
                            ->select("id", "city_name")
                            ->where("state_id", $state_id)
                            ->orderBy("city_name", "ASC")
                            ->pluck("city_name", "id")->toArray();
        $html = '';
        foreach ($cities as $id => $city_name) {
            $html.= '<option value="' . $id . '">' . $city_name . '</option>';
        }
        return response()->json([
            'status' => true,
            'message' => 'City List',
            'city_list' => $html
        ]);
    }
}
