<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Rumah Sakit - EmergencyChain')</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #383941;
            background: #fff5f5;
        }

        .hospital-header {
            height: 98px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid #eee;
        }

        .brand {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .brand span { color: #ed464d; }

        .brand-subtitle {
            font-size: 12px;
            color: #8a8b95;
            letter-spacing: 1.5px;
            margin-top: 4px;
        }

        .hospital-profile {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .hospital-avatar {
            width: 50px;
            height: 50px;
            background: #ffe5e7;
            color: #de343d;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-weight: bold;
        }

        .hospital-sidebar {
            position: fixed;
            top: 98px;
            bottom: 0;
            width: 300px;
            background: white;
            border-right: 1px solid #eee;
            padding: 46px 22px;
            display: flex;
            flex-direction: column;
        }

        .sidebar-label {
            font-size: 13px;
            font-weight: bold;
            color: #999aa4;
            letter-spacing: 1px;
            margin-bottom: 22px;
        }

        .nav-link {
            display: block;
            text-decoration: none;
            padding: 18px;
            border-radius: 12px;
            color: #686975;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .nav-link.active {
            background: #ffe5e7;
            color: #e53e45;
        }

        .logout-area {
            margin-top: auto;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .logout-button {
            border: 0;
            background: none;
            font-weight: bold;
            color: #868792;
            cursor: pointer;
            font-size: 16px;
        }

        .hospital-main {
            margin-left: 300px;
            padding: 34px 38px;
            min-height: calc(100vh - 98px);
        }

        @media (max-width: 768px) {
            .hospital-header {
                padding: 12px 18px;
                height: auto;
            }

            .brand { font-size: 21px; }

            .brand-subtitle { font-size: 9px; }

            .hospital-sidebar {
                position: static;
                width: 100%;
                padding: 14px;
            }

            .hospital-main {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
<header class="hospital-header">
    <div>
        <div class="brand">
            Emergency<span>Chain</span>
        </div>
        <div class="brand-subtitle">
            INTEGRATED RESPONSE SYSTEM
        </div>
    </div>

    <div class="hospital-profile">
        <div class="hospital-avatar">RS</div>
        <div>
            <strong>
                {{ auth()->user()->rumahSakit?->nama_rs ?? 'Rumah Sakit' }}
            </strong>
            <div style="color:#92939b;font-size:13px;margin-top:5px">
                Rumah Sakit
            </div>
        </div>
    </div>
</header>

<aside class="hospital-sidebar">
    <div class="sidebar-label">MENU UTAMA</div>

    <a href="{{ route('hospital.referrals.index') }}"
       class="nav-link {{ request()->routeIs('hospital.referrals.*') ? 'active' : '' }}">
        ▦ &nbsp; Dashboard
    </a>

    <a href="{{ route('hospital.referrals.index') }}"
       class="nav-link">
        ♡ &nbsp; Status Emergency
    </a>

    <a href="{{ route('hospital.referrals.index') }}"
       class="nav-link">
        ▤ &nbsp; Riwayat Penanganan
    </a>

    <div class="logout-area">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-button">
                ↪ &nbsp; Logout
            </button>
        </form>
    </div>
</aside>

<main class="hospital-main">
    @yield('content')
</main>
</body>
</html>
