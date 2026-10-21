<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - Pusat Kontrol Backup & QNAP Sync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #0b1120;
            --bg-card: rgba(30, 41, 59, 0.7);
            --border-card: rgba(51, 65, 85, 0.6);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.15);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.15);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.15);
            --font-sans: 'Plus Jakarta Sans', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(at 10% 10%, rgba(59, 130, 246, 0.12) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(16, 185, 129, 0.08) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 2.5rem 1.5rem;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-card);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }

        .logo-badge svg {
            width: 26px;
            height: 26px;
            fill: #fff;
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .badge-driver {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Actions */
        .actions-bar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.2rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(51, 65, 85, 0.6);
            color: #e2e8f0;
            border: 1px solid var(--border-card);
        }

        .btn-secondary:hover {
            background: rgba(71, 85, 105, 0.8);
            color: #fff;
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
            border-radius: 6px;
        }

        .btn-danger {
            background: var(--danger-bg);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.3);
            color: #fff;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: var(--success-bg);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #34d399;
        }

        .alert-error {
            background: var(--danger-bg);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #f87171;
        }

        /* Stats Grid */
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .card:hover {
            border-color: rgba(96, 165, 250, 0.4);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-db { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .icon-att { background: rgba(168, 85, 247, 0.2); color: #c084fc; }
        .icon-qnap { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .icon-summary { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
            word-break: break-all;
        }

        .stat-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            margin-top: 0.75rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            width: fit-content;
        }

        .status-pill.success {
            background: var(--success-bg);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-pill.failed {
            background: var(--danger-bg);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Tables Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .table-container {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        th {
            background: rgba(15, 23, 42, 0.6);
            color: var(--text-muted);
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-card);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(51, 65, 85, 0.4);
            color: #e2e8f0;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(51, 65, 85, 0.25);
        }

        .font-mono {
            font-family: var(--font-mono);
            font-size: 0.8rem;
        }

        .copy-tag {
            cursor: pointer;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            background: rgba(51, 65, 85, 0.6);
            border: 1px solid rgba(71, 85, 105, 0.6);
            transition: background 0.2s;
        }

        .copy-tag:hover {
            background: rgba(71, 85, 105, 0.9);
            color: #fff;
        }

        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <header class="header">
        <div class="header-title">
            <div class="logo-badge">
                <svg viewBox="0 0 24 24"><path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" style="display:none"/><path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z" style="display:none"/><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4C9.11 4 6.6 5.64 5.35 8.04C2.34 8.36 0 10.91 0 14C0 17.31 2.69 20 6 20H19C21.76 20 24 17.76 24 15C24 12.36 21.95 10.22 19.35 10.04ZM19 18H6C3.79 18 2 16.21 2 14C2 11.95 3.53 10.24 5.56 10.03L6.63 9.92L7.13 8.97C8.08 7.14 9.94 6 12 6C14.62 6 16.88 7.86 17.39 10.43L17.69 11.93L19.22 12.04C20.78 12.14 22 13.45 22 15C22 16.65 20.65 18 19 18Z"/><path d="M13 13.55V9H11V13.55L9.41 11.97L8 13.38L12 17.38L16 13.38L14.59 11.97L13 13.55Z"/></svg>
            </div>
            <div>
                <h1>Pusat Kontrol Backup: {{ $appName }}</h1>
                <div class="subtitle">
                    Driver Aktif: <span class="badge-driver">{{ $driver }}</span> &bull; 
                    Storage Disk: <code class="font-mono">{{ $storageDisk }}</code>
                </div>
            </div>
        </div>

        <!-- Manual Backup Triggers -->
        <div class="actions-bar">
            <form action="{{ route('app-backup.dashboard.run') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="type" value="database">
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" style="display:none"/><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
                    Backup Database Sekarang
                </button>
            </form>

            <form action="{{ route('app-backup.dashboard.run') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="type" value="attachments">
                <button type="submit" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13v6a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-6H3l9-9 9 9h-2zm-8 4h2v-4h-2v4z" style="display:none"/><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 0 1 5 0v10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5V6H9v9.5a3 3 0 0 0 6 0V5a4 4 0 0 0-8 0v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>
                    Backup Attachments
                </button>
            </form>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stat Cards Grid -->
    <div class="grid-stats">
        <!-- Database Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Database Terakhir</span>
                <div class="card-icon icon-db">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C7.58 3 4 4.79 4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7c0-2.21-3.58-4-8-4zm0 2c3.87 0 6 1.5 6 2s-2.13 2-6 2-6-1.5-6-2 2.13-2 6-2zm0 14c-3.87 0-6-1.5-6-2v-2.23c1.61.78 3.72 1.23 6 1.23s4.39-.45 6-1.23V17c0 .5-2.13 2-6 2zm0-5c-3.87 0-6-1.5-6-2v-2.23c1.61.78 3.72 1.23 6 1.23s4.39-.45 6-1.23V12c0 .5-2.13 2-6 2z"/></svg>
                </div>
            </div>
            @if($dbLatest)
                <div class="stat-value" title="{{ $dbLatest['filename'] }}">{{ $dbLatest['size_human'] }}</div>
                <div class="stat-meta">
                    <span class="font-mono">{{ Str::limit($dbLatest['filename'], 30) }}</span>
                    <span>Dibuat: {{ \Carbon\Carbon::parse($dbLatest['created_at'])->diffForHumans() }}</span>
                </div>
                <div style="margin-top: 1rem;">
                    <a href="{{ route('app-backup.dashboard.download', ['filename' => $dbLatest['filename'], 'type' => 'database']) }}" class="btn btn-secondary btn-sm">
                        Unduh SQL
                    </a>
                </div>
            @else
                <div class="stat-value" style="color: var(--text-muted);">Belum Tersedia</div>
                <div class="stat-meta">Klik "Backup Database Sekarang" untuk membuat dump pertama.</div>
            @endif
        </div>

        <!-- Attachments Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Attachments Terakhir</span>
                <div class="card-icon icon-att">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                </div>
            </div>
            @if($attLatest)
                <div class="stat-value" title="{{ $attLatest['filename'] }}">{{ $attLatest['size_human'] }}</div>
                <div class="stat-meta">
                    <span class="font-mono">{{ Str::limit($attLatest['filename'], 30) }}</span>
                    <span>Dibuat: {{ \Carbon\Carbon::parse($attLatest['created_at'])->diffForHumans() }}</span>
                </div>
                <div style="margin-top: 1rem;">
                    <a href="{{ route('app-backup.dashboard.download', ['filename' => $attLatest['filename'], 'type' => 'attachments']) }}" class="btn btn-secondary btn-sm">
                        Unduh ZIP
                    </a>
                </div>
            @else
                <div class="stat-value" style="color: var(--text-muted);">Belum Tersedia</div>
                <div class="stat-meta">Klik "Backup Attachments" untuk membuat arsip file publik.</div>
            @endif
        </div>

        <!-- QNAP Cloud Sync Status -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Status Sinkron QNAP</span>
                <div class="card-icon icon-qnap">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 20 6 20h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM10 17l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                </div>
            </div>
            @php
                $firstReport = !empty($reports) ? reset($reports) : null;
            @endphp
            @if($firstReport)
                <div class="stat-value">
                    @if($firstReport['status'] === 'success')
                        <span class="status-pill success">SYNC SUKSES</span>
                    @else
                        <span class="status-pill failed">SYNC GAGAL</span>
                    @endif
                </div>
                <div class="stat-meta">
                    <span>Service: <strong>{{ $firstReport['service'] }}</strong></span>
                    <span>Dilaporkan: {{ \Carbon\Carbon::parse($firstReport['reported_at'])->diffForHumans() }}</span>
                    @if(!empty($firstReport['uploaded_object']))
                        <span class="font-mono">Objek: {{ Str::limit($firstReport['uploaded_object'], 25) }}</span>
                    @endif
                </div>
            @else
                <div class="stat-value"><span class="status-pill failed">MENUNGGU WORKER</span></div>
                <div class="stat-meta">Belum ada laporan dari backup puller worker.</div>
            @endif
        </div>

        <!-- Total Storage Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Ringkasan Arsip</span>
                <div class="card-icon icon-summary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_size'] }}</div>
            <div class="stat-meta">
                <span>Total Berkas: <strong>{{ $stats['total_files'] }} file</strong></span>
                <span>Tipe Driver: <code>{{ $driver }}</code></span>
            </div>
        </div>
    </div>

    <!-- Available Backups List -->
    <div class="section-header">
        <h2 class="section-title">Daftar Berkas Backup Tersedia</h2>
    </div>

    <div class="table-container">
        @if(count($allBackups) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Nama Berkas</th>
                        <th>Ukuran</th>
                        <th>Integritas (SHA-256)</th>
                        <th>Waktu Pembuatan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allBackups as $backup)
                        <tr>
                            <td>
                                @if($backup['type'] === 'database')
                                    <span class="status-pill" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">DATABASE</span>
                                @else
                                    <span class="status-pill" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;">ATTACHMENT</span>
                                @endif
                            </td>
                            <td>
                                <strong class="font-mono">{{ $backup['filename'] }}</strong>
                            </td>
                            <td>{{ $backup['size_human'] }}</td>
                            <td>
                                <span class="font-mono copy-tag" onclick="copyToClipboard('{{ $backup['sha256'] }}')" title="Klik untuk menyalin SHA-256">
                                    {{ substr($backup['sha256'], 0, 8) }}...{{ substr($backup['sha256'], -8) }} ðŸ“‹
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($backup['created_at'])->format('d M Y H:i:s') }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem;">
                                    <a href="{{ route('app-backup.dashboard.download', ['filename' => $backup['filename'], 'type' => $backup['type']]) }}" class="btn btn-secondary btn-sm">
                                        Unduh
                                    </a>
                                    <form action="{{ route('app-backup.dashboard.delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus backup ini?');">
                                        @csrf
                                        <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.4; margin-bottom: 0.5rem;"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                <p>Belum ada berkas backup yang tersimpan di disk lokal.</p>
            </div>
        @endif
    </div>

    <!-- Worker Reports Section -->
    <div class="section-header">
        <h2 class="section-title">Riwayat Laporan Sinkronisasi Worker (Audit Trail)</h2>
    </div>

    <div class="table-container">
        @if(!empty($reports))
            <table>
                <thead>
                    <tr>
                        <th>Service Pelapor</th>
                        <th>Status</th>
                        <th>Berkas Sumber</th>
                        <th>Objek QNAP</th>
                        <th>Ukuran Terunggah</th>
                        <th>Waktu Laporan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $serviceName => $report)
                        <tr>
                            <td><strong>{{ $serviceName }}</strong></td>
                            <td>
                                @if($report['status'] === 'success')
                                    <span class="status-pill success">SUCCESS</span>
                                @else
                                    <span class="status-pill failed">FAILED</span>
                                @endif
                            </td>
                            <td class="font-mono">{{ $report['filename'] ?? '-' }}</td>
                            <td class="font-mono">{{ $report['uploaded_object'] ?? '-' }}</td>
                            <td>{{ isset($report['uploaded_size']) ? number_format($report['uploaded_size']) . ' B' : '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($report['reported_at'])->format('d M Y H:i:s') }}</td>
                            <td>
                                @if(!empty($report['error_message']))
                                    <span style="color: #f87171; font-size: 0.8rem;">{{ Str::limit($report['error_message'], 50) }}</span>
                                @else
                                    <span style="color: #34d399; font-size: 0.8rem;">Terunggah ke QNAP</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>Belum ada laporan audit dari service worker backup.</p>
            </div>
        @endif
    </div>

    <!-- Worker Integration Snippet -->
    <div class="section-header">
        <h2 class="section-title">Konfigurasi Worker Perantara (apps.json)</h2>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <span class="card-title">Snippet Konfigurasi Aplikasi</span>
            @if(!empty($apiKey))
                <button type="button" class="btn btn-secondary btn-sm" onclick="copyWorkerSnippet()">
                    Salin Snippet JSON ðŸ“‹
                </button>
            @endif
        </div>
        @if(!empty($apiKey))
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                Salin dan tempelkan blok JSON berikut ke dalam berkas <code>apps.json</code> di kontainer puller worker Anda:
            </p>
            <pre id="worker-snippet" class="font-mono" style="background: rgba(15, 23, 42, 0.8); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-card); overflow-x: auto; font-size: 0.85rem; color: #38bdf8;">{
  "name": "{{ strtolower(Str::slug($appName)) }}",
  "serviceName": "backup-puller-{{ strtolower(Str::slug($appName)) }}",
  "apiBaseUrl": "{{ $appUrl }}",
  "apiKey": "{{ $apiKey }}",
  "backupTypes": ["database", "attachments"],
  "storage": "qnap",
  "qnapDestPath": "/home/backup/{{ strtolower(Str::slug($appName)) }}",
  "uploadPolicy": "dedupe"
}</pre>
        @else
            <div style="color: #f87171; font-size: 0.875rem;">
                âš ï¸ <strong>APP_BACKUP_API_KEY</strong> belum di-generate di file <code>.env</code> aplikasi ini.
                <div style="margin-top: 0.5rem; color: var(--text-muted);">
                    Jalankan perintah ini di terminal server Anda untuk membuat kunci otomatis:
                    <br><code class="font-mono" style="background: rgba(15, 23, 42, 0.8); padding: 0.3rem 0.6rem; border-radius: 4px; display: inline-block; margin-top: 0.3rem; color: #38bdf8;">php artisan app-backup:key-generate</code>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Teks berhasil disalin ke clipboard:\n' + text);
        });
    }

    function copyWorkerSnippet() {
        const snippet = document.getElementById('worker-snippet').innerText;
        navigator.clipboard.writeText(snippet).then(() => {
            alert('Snippet JSON worker berhasil disalin ke clipboard!');
        });
    }
</script>

</body>
</html>