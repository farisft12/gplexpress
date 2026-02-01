<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GPLEXPRES</title>
    <link rel="icon" href="/favicon.ico">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8fafc;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 20px rgba(0, 32, 74, 0.1);
            padding: 1rem 2rem;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #00204A;
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            font-weight: 600;
            color: #00204A;
        }

        .user-role {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .role-admin {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
        }

        .role-staff {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
        }

        .role-customer {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background: #FF7B31;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #e5692a;
            transform: translateY(-1px);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .welcome-message {
            font-size: 2rem;
            font-weight: 700;
            color: #00204A;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #64748b;
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 32, 74, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #FF7B31, #ff9f5a);
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #8b5cf6, #a855f7);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #00204A;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 32, 74, 0.1);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #00204A;
            margin-bottom: 1.5rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            text-decoration: none;
            color: #00204A;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            border-color: #FF7B31;
            background: rgba(255, 123, 49, 0.05);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FF7B31, #ff9f5a);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 32, 74, 0.1);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e1e5e9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #f8fafc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: #00204A;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 0 1rem;
            }

            .welcome-message {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="{{ route('welcome') }}" class="logo">GPLEXPRES</a>
            <div class="user-info">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <span class="user-role role-{{ Auth::user()->role }}">
                    {{ ucfirst(Auth::user()->role) }}
                </span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 class="welcome-message">
                Selamat datang, {{ Auth::user()->name }}!
            </h1>
            <p class="welcome-subtitle">
                @if(Auth::user()->role === 'admin')
                    Panel administrator GPLEXPRES - Kelola sistem pengiriman
                @elseif(Auth::user()->role === 'staff')
                    Panel staff GPLEXPRES - Kelola pengiriman dan pelanggan
                @else
                    Dashboard pelanggan GPLEXPRES - Lacak dan kelola pengiriman Anda
                @endif
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            @if(Auth::user()->role === 'admin')
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value">5,678</div>
                    <div class="stat-label">Total Pengiriman</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">Rp 2.1M</div>
                    <div class="stat-label">Revenue Bulan Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">98.5%</div>
                    <div class="stat-label">Tingkat Kepuasan</div>
                </div>
            @elseif(Auth::user()->role === 'staff')
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-value">45</div>
                    <div class="stat-label">Pengiriman Hari Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">38</div>
                    <div class="stat-label">Terkirim</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">7</div>
                    <div class="stat-label">Dalam Perjalanan</div>
                </div>
            @else
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-label">Total Pengiriman</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">10</div>
                    <div class="stat-label">Berhasil Terkirim</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-value">2</div>
                    <div class="stat-label">Dalam Perjalanan</div>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title">Aksi Cepat</h2>
            <div class="actions-grid">
                @if(Auth::user()->role === 'admin')
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <span>Kelola Pengguna</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span>Laporan Analytics</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span>Pengaturan Sistem</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <span>Backup Data</span>
                    </a>
                @elseif(Auth::user()->role === 'staff')
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span>Buat Pengiriman Baru</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span>Lacak Paket</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <span>Verifikasi Pengiriman</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <span>Customer Support</span>
                    </a>
                @else
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span>Kirim Paket Baru</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span>Lacak Paket</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <span>Cek Ongkos Kirim</span>
                    </a>
                    <a href="#" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <span>Riwayat Pengiriman</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2 class="section-title">Aktivitas Terbaru</h2>
            
            @if(Auth::user()->role === 'admin')
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Pengguna baru terdaftar: customer@example.com</div>
                        <div class="activity-time">2 menit yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Pengiriman GPL123456 berhasil dikirim</div>
                        <div class="activity-time">15 menit yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Laporan harian berhasil dibuat</div>
                        <div class="activity-time">1 jam yang lalu</div>
                    </div>
                </div>
            @elseif(Auth::user()->role === 'staff')
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Paket GPL123456 telah diverifikasi</div>
                        <div class="activity-time">5 menit yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Rute pengiriman Jakarta-Bandung dimulai</div>
                        <div class="activity-time">30 menit yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Customer support ticket #456 ditangani</div>
                        <div class="activity-time">1 jam yang lalu</div>
                    </div>
                </div>
            @else
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Paket GPL789012 sedang dalam perjalanan</div>
                        <div class="activity-time">1 jam yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Paket GPL345678 berhasil terkirim</div>
                        <div class="activity-time">3 jam yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Pengiriman baru GPL901234 telah dibuat</div>
                        <div class="activity-time">1 hari yang lalu</div>
                    </div>
                </div>
            @endif
        </div>
    </main>
</body>
</html>
