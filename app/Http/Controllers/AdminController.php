<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pickup;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pickups = Pickup::with('user', 'handledBy')->latest()->get();
        return view('admin.dashboard', compact('pickups'));
    }

    public function realtimeData()
    {
        $pickups = Pickup::with('user', 'handledBy')->latest()->get();
        
        $totalPickups    = $pickups->count();
        $pendingPickups  = $pickups->where('status', 'pending')->count();
        $approvedPickups = $pickups->where('status', 'approved')->count();
        $rejectedPickups = $pickups->where('status', 'rejected')->count();
        $totalWeight     = $pickups->where('status', 'approved')->sum('weight');
        $todayStr        = \Carbon\Carbon::today()->format('Y-m-d');
        $todayWeight     = $pickups->where('status', 'approved')
            ->filter(fn($item) => \Carbon\Carbon::parse($item->pickup_date)->format('Y-m-d') === $todayStr)
            ->sum('weight');
        $totalUsers      = $pickups->pluck('user_id')->unique()->count();
        $approvalRate    = $totalPickups > 0 ? round(($approvedPickups / $totalPickups) * 100, 1) : 0;

        $wasteByType = [];
        foreach(['Plastik','Kertas','Logam','Kaca','Organik','Elektronik','Lainnya'] as $type) {
            $wasteByType[$type] = $pickups->where('type', $type)->where('status','approved')->sum('weight');
        }

        $last7Days = [];
        for($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i)->format('Y-m-d');
            $last7Days[\Carbon\Carbon::parse($date)->format('D')] = $pickups->where('status','approved')
                ->filter(fn($item) => \Carbon\Carbon::parse($item->pickup_date)->format('Y-m-d') === $date)
                ->sum('weight');
        }

        // Format pickups for direct JSON consumption
        $formattedPickups = $pickups->map(function($pickup) {
            return [
                'id' => $pickup->id,
                'user_name' => optional($pickup->user)->name ?? 'User',
                'user_email' => optional($pickup->user)->email ?? '',
                'user_photo' => optional($pickup->user)->getPhotoUrl() ?? '',
                'type' => $pickup->type,
                'weight' => $pickup->weight,
                'pickup_date' => \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y'),
                'pickup_date_raw' => \Carbon\Carbon::parse($pickup->pickup_date)->format('Y-m-d'),
                'address' => $pickup->address,
                'phone' => $pickup->phone,
                'status' => $pickup->status,
                'points' => $pickup->points_earned,
                'notes' => $pickup->notes,
                'photo' => $pickup->photo ? \Illuminate\Support\Facades\Storage::url($pickup->photo) : null,
                'latitude' => $pickup->latitude,
                'longitude' => $pickup->longitude,
                'handled_by' => optional($pickup->handledBy)->name,
                'maps_url' => $pickup->latitude ? "https://maps.google.com/?q={$pickup->latitude},{$pickup->longitude}" : null,
            ];
        });

        return response()->json([
            'totalPickups' => $totalPickups,
            'pendingPickups' => $pendingPickups,
            'approvedPickups' => $approvedPickups,
            'rejectedPickups' => $rejectedPickups,
            'totalWeight' => $totalWeight,
            'todayWeight' => $todayWeight,
            'totalUsers' => $totalUsers,
            'approvalRate' => $approvalRate,
            'wasteByType' => $wasteByType,
            'last7Days' => $last7Days,
            'pickups' => $formattedPickups
        ]);
    }
}