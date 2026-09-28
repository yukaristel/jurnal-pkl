<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-active: #1e293b;
            --accent: #6366f1;
        }
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        .app-shell { display: flex; min-height: 100vh; }

        /* Sidebar */
        .app-sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            flex-shrink: 0;
            box-shadow: 2px 0 8px rgba(0,0,0,.04);
        }
        .app-sidebar .brand {
            padding: 1.25rem 1.25rem;
            font-weight: 700;
            font-size: 1.05rem;
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .app-sidebar .brand i { color: #818cf8; }
        .app-sidebar .nav-link {
            color: #cbd5e1;
            padding: .7rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            border-left: 3px solid transparent;
            text-decoration: none;
            transition: all .15s;
            font-size: .92rem;
        }
        .app-sidebar .nav-link:hover { background: rgba(255,255,255,.05); color: #fff; }
        .app-sidebar .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            border-left-color: #818cf8;
        }
        .app-sidebar .nav-link i { font-size: 1.05rem; }
        .app-sidebar .nav-section {
            padding: 1rem 1.25rem .35rem;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            font-weight: 600;
        }

        /* Topbar */
        .app-main { flex: 1; min-width: 0; }
        .app-topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 2px rgba(0,0,0,.02);
        }
        .app-content { padding: 1.75rem; }

        /* Stat Cards — compact style */
        .stat-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            color: #fff;
            transition: transform .15s, box-shadow .15s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,.12) !important;
        }
        .stat-card .card-body { padding: .9rem 1.15rem; position: relative; z-index: 1; }
        .stat-card .stat-icon {
            position: absolute;
            right: -8px;
            bottom: -12px;
            font-size: 3.5rem;
            opacity: .18;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            opacity: .9;
            margin-bottom: .25rem;
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }
        .stat-card .stat-sub {
            font-size: .72rem;
            opacity: .85;
            margin-top: .2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-card.compact .stat-value { font-size: 1.4rem; }

        /* Plain card */
        .card-flat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.03);
        }
        .card-flat .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            padding: .85rem 1.15rem;
            font-weight: 600;
            font-size: .92rem;
        }
        .bg-grad-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        .bg-grad-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-grad-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .bg-grad-danger  { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .bg-grad-info    { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .bg-grad-purple  { background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%); }
        .bg-grad-pink    { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }
        .bg-grad-teal    { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); }

        /* Activity item */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: .85rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-item .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .activity-item .info { flex: 1; min-width: 0; }
        .activity-item .info .name { font-weight: 600; color: #0f172a; }
        .activity-item .info .desc { font-size: .82rem; color: #64748b; }
        .activity-item .time { font-size: .75rem; color: #94a3b8; white-space: nowrap; }

        @media (max-width: 768px) {
            .app-shell { flex-direction: column; }
            .app-sidebar { width: 100%; }
            .stat-card .stat-value { font-size: 1.75rem; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    @include('layouts.partials.sidebar')
    <div class="app-main">
        <div class="app-topbar">
            <div>
                <h5 class="m-0 font-weight-bold text-dark">@yield('page-title', 'Dashboard')</h5>
                @hasSection('page-subtitle')
                    <small class="text-muted">@yield('page-subtitle')</small>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted d-none d-sm-inline">
                    <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        <div class="app-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>