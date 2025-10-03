<?php

namespace App\Services\Audits;

use Illuminate\Http\Request;

interface AuditInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array;
}
