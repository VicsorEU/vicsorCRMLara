<?php

namespace App\Services\Audits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class AuditService implements AuditInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array
    {
        $search = $request->get('search');
        $modelSearch = $request->get('model');
        $event = $request->get('event');

        $query = Activity::query()->with('causer');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('description::text ILIKE ?', ["%$search%"])
                    ->orWhereRaw('log_name::text ILIKE ?', ["%$search%"])
                    ->orWhereRaw('subject_type::text ILIKE ?', ["%$search%"])
                    ->orWhereRaw('subject_id::text ILIKE ?', ["%$search%"]);
            });
        }

        if ($modelSearch) {
            $query->where('subject_type', $modelSearch);
        }

        if ($event) {
            $query->where('event', $event);
        }

        $items = $query->latest()->paginate(20)->withQueryString();
        $models = Activity::query()->select('subject_type')->distinct()->pluck('subject_type')->filter()->values();

        return [
            'success' => true,
            'items' => $items,
            'models' => $models,
        ];
    }
}
