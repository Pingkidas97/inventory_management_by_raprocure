<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
class NotificationController extends Controller
{
     /**
     * Constructor to check user authorization.
     */
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 2) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(Request $request) {
        $query = Notification::with(['users', 'senders']);

        if ($request->filled('user')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
            });
        }

        if ($request->filled('sender')) {
            $query->whereHas('senders', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('sender') . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $query->where('user_id',getParentUserId());

        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $notifications = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('vendor.notification.partials.table', compact('notifications'))->render();
        }
        return view('vendor.notification.index', compact('notifications'));
    }
}
