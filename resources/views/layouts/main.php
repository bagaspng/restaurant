<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\View::escape($title ?? 'Sistem Pemesanan Restoran') ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #212529;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .nav-link {
            font-weight: 500;
        }
        .nav-link.active {
            color: #ffc107 !important;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar Sistem Lokal (Aktor Tunggal: Administrator) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <i class="bi bi-cup-hot-fill text-warning"></i>
                <span><?= \App\Helpers\View::escape(\Config\Config::get('app_name', 'Sistem Restoran')) ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/reservations"><i class="bi bi-calendar-check me-1"></i> Reservasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/orders"><i class="bi bi-cart3 me-1"></i> Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tables"><i class="bi bi-grid-3x3 me-1"></i> Meja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/menus"><i class="bi bi-egg-fried me-1"></i> Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/reports"><i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <span class="badge bg-warning text-dark px-3 py-2 fw-semibold">
                            <i class="bi bi-shield-check me-1"></i> Administrator
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Kontainer Pesan Flash -->
    <div class="container mt-3">
        <?php if (\App\Helpers\Session::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= \App\Helpers\View::escape(\App\Helpers\Session::getFlash('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (\App\Helpers\Session::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= \App\Helpers\View::escape(\App\Helpers\Session::getFlash('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Konten Utama -->
    <main class="container my-4 flex-grow-1">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 mt-auto text-center text-muted small">
        <div class="container">
            <span>&copy; <?= date('Y') ?> <?= \App\Helpers\View::escape(\Config\Config::get('app_name')) ?> — Mode Lokal Administrator — Portofolio Asesmen Pemrograman BNSP</span>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
