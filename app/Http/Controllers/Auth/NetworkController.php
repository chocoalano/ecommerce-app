<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $title = 'Jaringan Binary';
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Jaringan Binary', 'href' => null],
        ];
        // base query: find descendants of current customer
        $query = $customer->descendants;
        $title = 'Jaringan Binary Member Aktif';
        // map to lightweight structure for view
        $members = collect($query)->flatMap(function ($member) use ($customer) {

            // Fungsi rekursif untuk memproses anak-anaknya
            $processMember = function ($member, $parentName = null) use (&$processMember) {
                $born = $member['created_at'] ? Carbon::parse($member['created_at'])->format('Y') : null;
                $death = $member['updated_at'] ? Carbon::parse($member['updated_at'])->format('Y') : null;

                // Data utama member
                $data = [[
                    'id' => $member['id'],
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'is_active' => (bool) ($member['is_active'] ?? false),
                    'born' => $born,
                    'death' => $death,
                    'depth' => $member['depth'] ?? null,
                    'parent' => $parentName,
                ]];

                // Jika ada anak-anak, proses mereka juga
                if (!empty($member['children'])) {
                    foreach ($member['children'] as $child) {
                        $data = array_merge($data, $processMember($child, $member['name']));
                    }
                }

                return $data;
            };

            // Proses root member
            return $processMember($member, $customer->name);
        })->values()->toArray();
        array_push($members, ['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email, 'is_active' => (bool) $customer->is_active, 'born' => $customer->created_at ? $customer->created_at->format('Y') : null, 'death' => $customer->updated_at ? $customer->updated_at->format('Y') : null, 'depth' => 0, 'parent' => null]);
        return view('pages.auth.profile.network.network_binary', compact('customer', 'breadcrumbs', 'title', 'members'));
    }
    public function sponsorship()
    {
        return view('pages.auth.profile.network.network_sponsorship');
    }
}
