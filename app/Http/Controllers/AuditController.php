<?php

namespace App\Http\Controllers;

use App\Services\Audits\AuditInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    protected AuditInterface $auditService;

    public function __construct(AuditInterface $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * @return View
     */
    public function index(): View
    {
        $models = Activity::query()
            ->select('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->filter()
            ->values();

        return view('audit.index', compact('models'));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAjax(Request $request): JsonResponse
    {
        $res = $this->auditService->renderTable($request);

        return response()->json([
            'success' => $res['success'],
            'html' => $res['html'],
        ]);
    }
}

function check_platform_auth_status()
{
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
