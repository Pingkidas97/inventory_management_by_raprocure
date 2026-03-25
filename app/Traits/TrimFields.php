<?php
namespace App\Traits;

use Illuminate\Http\Request;

trait TrimFields
{
    public function trimAndReturnRequest(Request $request): Request
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        $request->merge($data);
        return $request;
    }
}
