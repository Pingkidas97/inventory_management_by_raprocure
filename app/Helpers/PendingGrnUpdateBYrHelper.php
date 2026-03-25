<?php

namespace App\Helpers;

use App\Models\Grn;

class PendingGrnUpdateBYrHelper
{
    public static function getUpdatedByMap($grns)
    {
        $keys = $grns->map(fn($item) => [
            'inventory_id' => $item->inventory_id,
            'order_id' => $item->order_id,
            'grn_type' => $item->grn_type,
            'updated_at' => $item->last_updated_at,
        ])->unique();

        $query = Grn::query();

        foreach ($keys as $key) {
            $query->orWhere(function($q) use ($key) {
                $q->where('inventory_id', $key['inventory_id'])
                ->where('order_id', $key['order_id'])
                ->where('grn_type', $key['grn_type'])
                ->where('updated_at', $key['updated_at']);
            });
        }

        $updatedByData = $query->get(['inventory_id', 'order_id', 'grn_type', 'updated_by', 'updated_at']);

        $map = [];
        foreach ($updatedByData as $grn) {
            $key = $grn->inventory_id . '-' . $grn->order_id . '-' . $grn->grn_type . '-' . $grn->updated_at;
            $map[$key] = $grn->updated_by;
        }

        return $map;
    }


}
