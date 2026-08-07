<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Taply platform administration dashboard." />
  <title>Platform Overview — Taply Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/dashboard.js'])
</head>
<body class="admin-page">
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-head"><a class="brand" href="/"><span class="brand-mark">t</span>taply<span class="brand-dot">.</span></a><div class="workspace-pill"><span class="workspace-avatar">AD</span><div><b>Platform admin</b><small>Super administrator</small></div><i data-lucide="shield-check"></i></div></div>
      <nav class="side-nav" aria-label="Admin navigation">
        <span class="side-label">PLATFORM</span>
        <a class="active" href="/admin"><i data-lucide="layout-dashboard"></i> Overview</a>
        <a href="#users"><i data-lucide="users"></i> User management <span class="badge">12</span></a>
        <a href="#revenue"><i data-lucide="badge-dollar-sign"></i> Subscriptions</a>
        <a href="#payments"><i data-lucide="circle-dollar-sign"></i> Transactions</a>
        <a href="#"><i data-lucide="ticket-percent"></i> Coupons</a>
        <span class="side-label">EXPERIENCE</span>
        <a href="#"><i data-lucide="palette"></i> Themes</a>
        <a href="#"><i data-lucide="globe-2"></i> Custom domains</a>
        <a href="#"><i data-lucide="bar-chart-3"></i> Analytics</a>
        <span class="side-label">SYSTEM</span>
        <a href="#system"><i data-lucide="settings-2"></i> Website settings</a>
        <a href="#"><i data-lucide="mail"></i> Notifications</a>
        <a href="/dashboard"><i data-lucide="panel-left"></i> User dashboard</a>
      </nav>
      <div class="sidebar-bottom"><div class="sidebar-profile"><span class="workspace-avatar">SA</span><div><b>Super Admin</b><small>admin@taply.me</small></div><i data-lucide="more-horizontal"></i></div></div>
    </aside>

    <main class="app-main">
      <header class="app-topbar"><div style="display:flex;align-items:center;gap:12px"><button class="mobile-sidebar-toggle" id="mobile-sidebar-toggle" aria-label="Open navigation"><i data-lucide="menu"></i></button><div class="breadcrumb"><span>Administration</span><i data-lucide="chevron-right"></i><b>Platform overview</b></div></div><div class="topbar-actions"><div class="admin-status"><i></i> All systems operational</div><button class="top-icon" aria-label="Notifications"><i data-lucide="bell"></i><i></i></button><a class="view-card-button" href="/" target="_blank"><i data-lucide="external-link"></i><span>View website</span></a></div></header>

      <div class="dashboard-content">
        <div class="welcome-row"><div><div class="admin-kicker">THURSDAY, AUGUST 6</div><h1>Platform overview</h1><p>Monitor members, revenue, and system activity across Taply.</p></div><button class="date-filter"><i data-lucide="calendar-range"></i> August 2026 <i data-lucide="chevron-down"></i></button></div>

        <section class="stats-grid"><article class="stat-card"><div class="stat-head"><span class="stat-icon green"><i data-lucide="users"></i></span><span class="trend">↗ 14.2%</span></div><div class="stat-value">12,842</div><div class="stat-label">Total users</div></article><article class="stat-card"><div class="stat-head"><span class="stat-icon purple"><i data-lucide="crown"></i></span><span class="trend">↗ 9.8%</span></div><div class="stat-value">3,218</div><div class="stat-label">Paid subscribers</div></article><article class="stat-card"><div class="stat-head"><span class="stat-icon yellow"><i data-lucide="badge-dollar-sign"></i></span><span class="trend">↗ 21.4%</span></div><div class="stat-value">$38.6k</div><div class="stat-label">Monthly revenue</div></article><article class="stat-card"><div class="stat-head"><span class="stat-icon orange"><i data-lucide="globe-2"></i></span><span class="trend">↗ 6.1%</span></div><div class="stat-value">486</div><div class="stat-label">Custom domains</div></article></section>

        <section class="panel" id="users" style="margin-top:13px">
          <div class="panel-head"><div><h2>Newest members</h2><p>Recently registered users and subscription status</p></div><div style="display:flex;gap:13px"><button><i data-lucide="search"></i> Search</button><button>View all <i data-lucide="arrow-right"></i></button></div></div>
          <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>User</th><th>Plan</th><th>Status</th><th>Joined</th><th>Cards</th><th></th></tr></thead><tbody><tr><td><div class="user-cell"><span>NS</span><div><b>Nusrat Sultana</b><small>nusrat@form.com</small></div></div></td><td><span class="plan-pill pro">Professional</span></td><td><span class="status-pill"><i></i> Active</span></td><td>Aug 6, 2026</td><td>1</td><td><button class="table-action">Review</button></td></tr><tr><td><div class="user-cell"><span>AR</span><div><b>Ashik Rahman</b><small>ashik@novo.io</small></div></div></td><td><span class="plan-pill">Starter</span></td><td><span class="status-pill"><i></i> Active</span></td><td>Aug 6, 2026</td><td>1</td><td><button class="table-action">Review</button></td></tr><tr><td><div class="user-cell"><span>SF</span><div><b>Studio Form</b><small>hello@studioform.co</small></div></div></td><td><span class="plan-pill pro">Business</span></td><td><span class="status-pill pending"><i></i> Verification</span></td><td>Aug 5, 2026</td><td>4</td><td><button class="table-action">Verify</button></td></tr><tr><td><div class="user-cell"><span>MK</span><div><b>Mehedi Khan</b><small>mkhan@gmail.com</small></div></div></td><td><span class="plan-pill pro">Professional</span></td><td><span class="status-pill"><i></i> Active</span></td><td>Aug 5, 2026</td><td>2</td><td><button class="table-action">Review</button></td></tr></tbody></table></div>
        </section>

        <div class="admin-lower-grid">
          <section class="panel" id="payments"><div class="panel-head"><div><h2>Recent transactions</h2><p>Subscription and manual payment activity</p></div><a href="#">View all <i data-lucide="arrow-right"></i></a></div><div class="admin-table-wrap"><table class="admin-table" style="min-width:560px"><thead><tr><th>Transaction</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead><tbody><tr><td><div class="user-cell"><span><i data-lucide="credit-card"></i></span><div><b>#TXN-84291</b><small>Nusrat Sultana</small></div></div></td><td>Stripe</td><td><b>$9.00</b></td><td><span class="status-pill"><i></i> Paid</span></td></tr><tr><td><div class="user-cell"><span><i data-lucide="smartphone"></i></span><div><b>#TXN-84290</b><small>Studio Form</small></div></div></td><td>bKash</td><td><b>$29.00</b></td><td><span class="status-pill pending"><i></i> Review</span></td></tr><tr><td><div class="user-cell"><span><i data-lucide="credit-card"></i></span><div><b>#TXN-84289</b><small>Mehedi Khan</small></div></div></td><td>PayPal</td><td><b>$9.00</b></td><td><span class="status-pill"><i></i> Paid</span></td></tr></tbody></table></div></section>
          <section class="panel" id="revenue"><div class="panel-head"><div><h2>Revenue breakdown</h2><p>Current billing cycle</p></div><button><i data-lucide="more-horizontal"></i></button></div><div class="revenue-breakdown"><div class="revenue-total">$38,642 <small>↗ 21.4%</small></div><div class="bar-list"><div><div class="bar-item-head"><span>Professional</span><b>$21,418 · 55%</b></div><div class="bar-track"><div class="bar-fill" style="width:55%"></div></div></div><div><div class="bar-item-head"><span>Business</span><b>$14,236 · 37%</b></div><div class="bar-track"><div class="bar-fill coral" style="width:37%"></div></div></div><div><div class="bar-item-head"><span>NFC products</span><b>$2,988 · 8%</b></div><div class="bar-track"><div class="bar-fill purple" style="width:8%"></div></div></div></div></div></section>
        </div>

        <section class="panel" id="system" style="margin-top:13px"><div class="panel-head"><div><h2>System health</h2><p>Queue, scheduler, storage, and connected services</p></div><span class="admin-status"><i></i> Healthy</span></div><div class="system-list" style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px"><div class="system-row"><div><i data-lucide="list-restart"></i> Queue workers</div><span class="system-good">Running</span></div><div class="system-row"><div><i data-lucide="clock-3"></i> Scheduler</div><span class="system-good">On time</span></div><div class="system-row"><div><i data-lucide="database"></i> MySQL</div><span class="system-good">24 ms</span></div><div class="system-row"><div><i data-lucide="hard-drive"></i> Media storage</div><span class="system-good">42% used</span></div></div></section>
      </div>
    </main>
  </div>
  <nav class="app-mobile-nav"><a class="active" href="/admin"><i data-lucide="layout-dashboard"></i>Overview</a><a href="#users"><i data-lucide="users"></i>Users</a><a href="#payments"><i data-lucide="circle-dollar-sign"></i>Payments</a><a href="#system"><i data-lucide="settings-2"></i>System</a></nav>
  <div class="toast" id="toast"><i data-lucide="check-circle-2"></i><span>Action completed</span></div>
</body>
</html>
