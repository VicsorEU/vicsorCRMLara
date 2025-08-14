<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $r)
    {
        $q = Activity::query()->with('causer');

        if ($s = trim((string)$r->search)) {
            $q->where(function($qq) use ($s) {
                $qq->where('description','ilike',"%$s%")
                    ->orWhere('log_name','ilike',"%$s%")
                    ->orWhere('subject_type','ilike',"%$s%")
                    ->orWhere('subject_id','::text ilike',"%$s%"); // PG: текстовое сравнение
            });
        }
        if ($m = $r->get('model')) {
            $q->where('subject_type', $m);
        }
        if ($e = $r->get('event')) {
            $q->where('event', $e);
        }

        $items = $q->latest()->paginate(20)->withQueryString();

        $models = Activity::query()->select('subject_type')->distinct()->pluck('subject_type')->filter()->values();

        return view('audit.index', compact('items','models'));
    }
}

function check_platform_auth_status() {
    $api_url = get_custom_api_url() . 'users/me/';
    $token = isset($_COOKIE['token']) ? sanitize_text_field($_COOKIE['token']) : null;

    if (!$token) {
        return ['is_authenticated' => false];
    }

    $response = wp_remote_get($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);

    if (is_wp_error($response)) {
        return ['is_authenticated' => false];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($body['is_authenticated']) && $body['is_authenticated']) {
        return [
            'is_authenticated' => true,
            'user' => [
                'logo' => $body['logo'] ?? '',
            ],
        ];
    }

    return ['is_authenticated' => false];
}
