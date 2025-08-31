<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Access Debug</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font: 14px/1.4 -apple-system,Segoe UI,Roboto,Arial,sans-serif; padding:24px; background:#f6f7f9; color:#111; }
        .card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px; max-width:900px; margin:0 auto; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        h1 { margin:0 0 14px; font-size:20px; }
        pre { background:#0b1020; color:#e6f3ff; padding:12px; border-radius:12px; overflow:auto; }
        .grid { display:grid; grid-template-columns: 220px 1fr; gap:8px 16px; }
        .k { color:#6b7280; }
        .ok { color:#059669; font-weight:600; }
        .no { color:#dc2626; font-weight:600; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    </style>
</head>
<body>
<div class="card">
    <h1>Access Debug</h1>
    <div class="grid">
        <div class="k">User ID</div><div class="mono">{{ $user['id'] ?? '—' }}</div>
        <div class="k">Email</div><div class="mono">{{ $user['email'] ?? '—' }}</div>
        <div class="k">Role ID</div><div class="mono">{{ $user['access_role_id'] ?? '—' }}</div>
        <div class="k">Blocked at</div><div class="mono">{{ $user['blocked_at'] ?? 'null' }}</div>

        <div class="k">Role slug</div><div class="mono">{{ $role['slug'] ?? '—' }}</div>
        <div class="k">Role name</div><div class="mono">{{ $role['name'] ?? '—' }}</div>

        <div class="k">Resource</div><div class="mono">{{ $resource }}</div>
        <div class="k">Computed perms</div><div class="mono">{{ implode(', ', $computed_permissions ?? []) }}</div>

        <div class="k">can(view)</div><div>{!! ($can['view'] ?? false) ? '<span class="ok">YES</span>' : '<span class="no">NO</span>' !!}</div>
        <div class="k">can(own)</div><div>{!! ($can['own']  ?? false) ? '<span class="ok">YES</span>' : '<span class="no">NO</span>' !!}</div>
        <div class="k">can(full)</div><div>{!! ($can['full'] ?? false) ? '<span class="ok">YES</span>' : '<span class="no">NO</span>' !!}</div>
    </div>

    <h3 style="margin-top:18px">Abilities (raw from DB)</h3>
    <pre class="mono">{{ json_encode($role['abilities'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

    <p style="margin-top:12px;color:#6b7280">Попробуй ресурсы:
        <a href="{{ route('__access', ['resource' => 'settings']) }}">settings</a> ·
        <a href="{{ route('__access', ['resource' => 'projects']) }}">projects</a>
    </p>
</div>
</body>
</html>
