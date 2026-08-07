<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Manage your Taply digital card, leads, appointments, and analytics." />
  <title>Overview — Taply Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/dashboard.js'])
</head>
<body class="dashboard-page">
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-head">
        <a class="brand" href="/"><span class="brand-mark">t</span>taply<span class="brand-dot">.</span></a>
        <div class="workspace-pill"><span class="workspace-avatar">HA</span><div><b>Himel's workspace</b><small>Professional plan</small></div><i data-lucide="chevrons-up-down"></i></div>
      </div>
      <nav class="side-nav" aria-label="Dashboard navigation">
        <span class="side-label">WORKSPACE</span>
        <a class="active" href="/dashboard"><i data-lucide="layout-dashboard"></i> Overview</a>
        <a href="#card-preview" data-drawer-open><i data-lucide="contact-round"></i> My digital card</a>
        <a href="#quick-actions"><i data-lucide="blocks"></i> Content</a>
        <a href="#analytics"><i data-lucide="bar-chart-3"></i> Analytics</a>
        <span class="side-label">ENGAGE</span>
        <a href="#recent-activity"><i data-lucide="users"></i> Leads <span class="badge">6</span></a>
        <a href="#appointments"><i data-lucide="calendar-days"></i> Appointments <span class="badge">2</span></a>
        <a href="#"><i data-lucide="credit-card"></i> Payments</a>
        <span class="side-label">ACCOUNT</span>
        <a href="#"><i data-lucide="palette"></i> Themes</a>
        <a href="#"><i data-lucide="globe-2"></i> Custom domain</a>
        <a href="#"><i data-lucide="settings-2"></i> Settings</a>
      </nav>
      <div class="sidebar-bottom">
        <div class="upgrade-card"><span><i data-lucide="sparkles"></i></span><b>You're on Professional</b><p>Unlock team tools and centralized card management.</p><a href="#">Explore Business <i data-lucide="arrow-right"></i></a></div>
        <div class="sidebar-profile"><img src="/images/himel.jpg" alt="Himel Ahmed"><div><b>Himel Ahmed</b><small>himel@studio.com</small></div><i data-lucide="more-horizontal"></i></div>
      </div>
    </aside>

    <main class="app-main">
      <header class="app-topbar">
        <div style="display:flex;align-items:center;gap:12px"><button class="mobile-sidebar-toggle" id="mobile-sidebar-toggle" aria-label="Open navigation"><i data-lucide="menu"></i></button><div class="breadcrumb"><span>Workspace</span><i data-lucide="chevron-right"></i><b>Overview</b></div></div>
        <div class="topbar-actions"><button class="top-icon" aria-label="Notifications"><i data-lucide="bell"></i><i></i></button><a class="view-card-button" href="/himel" target="_blank"><i data-lucide="external-link"></i><span>View live card</span></a></div>
      </header>

      <div class="dashboard-content">
        <div class="welcome-row"><div><h1>Good evening, Himel.</h1><p>Here's how your digital presence is performing this week.</p></div><button class="date-filter"><i data-lucide="calendar-range"></i> Last 7 days <i data-lucide="chevron-down"></i></button></div>

        <section class="stats-grid" aria-label="Profile statistics">
          <article class="stat-card"><div class="stat-head"><span class="stat-icon green"><i data-lucide="eye"></i></span><span class="trend">↗ 18.4%</span></div><div class="stat-value">1,284</div><div class="stat-label">Profile views</div></article>
          <article class="stat-card"><div class="stat-head"><span class="stat-icon orange"><i data-lucide="mouse-pointer-click"></i></span><span class="trend">↗ 12.7%</span></div><div class="stat-value">346</div><div class="stat-label">Link taps</div></article>
          <article class="stat-card"><div class="stat-head"><span class="stat-icon purple"><i data-lucide="user-plus"></i></span><span class="trend">↗ 8.2%</span></div><div class="stat-value">48</div><div class="stat-label">New leads</div></article>
          <article class="stat-card"><div class="stat-head"><span class="stat-icon yellow"><i data-lucide="contact"></i></span><span class="trend">↗ 5.1%</span></div><div class="stat-value">89</div><div class="stat-label">Contact saves</div></article>
        </section>

        <div class="dashboard-grid">
          <div style="display:flex;flex-direction:column;gap:13px;min-width:0">
            <section class="panel analytics-panel" id="analytics">
              <div class="panel-head"><div><h2>Profile performance</h2><p>Views and link taps over the last 7 days</p></div><button>View report <i data-lucide="arrow-up-right"></i></button></div>
              <div class="analytics-summary"><div class="summary-metric"><span class="summary-dot"></span><div><small>Profile views</small><b>1,284</b></div></div><div class="summary-metric"><span class="summary-dot secondary"></span><div><small>Link taps</small><b>346</b></div></div></div>
              <div class="dashboard-chart"><div class="y-axis"><span>300</span><span>200</span><span>100</span><span>0</span></div><div class="chart-plot"><svg viewBox="0 0 680 180" preserveAspectRatio="none" aria-label="Profile views chart"><path class="area" d="M0,145 C70,138 85,105 150,112 S245,140 298,82 S385,68 442,92 S545,42 590,56 S640,22 680,31 L680,180 L0,180 Z"></path><path class="line" d="M0,145 C70,138 85,105 150,112 S245,140 298,82 S385,68 442,92 S545,42 590,56 S640,22 680,31"></path><circle cx="680" cy="31" r="4" fill="#466c5a"></circle></svg><div class="chart-labels"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div></div></div>
            </section>

            <section class="panel" id="quick-actions">
              <div class="panel-head"><div><h2>Quick actions</h2><p>Keep your card fresh and ready to share</p></div></div>
              <div class="quick-actions"><button class="quick-action" data-drawer-open><span><i data-lucide="pencil-line"></i></span>Edit profile</button><button class="quick-action" data-drawer-open><span><i data-lucide="image-plus"></i></span>Add portfolio</button><button class="quick-action" id="dashboard-share"><span><i data-lucide="share-2"></i></span>Share card</button><button class="quick-action" id="dashboard-qr"><span><i data-lucide="qr-code"></i></span>Download QR</button></div>
            </section>

            <section class="panel" id="recent-activity">
              <div class="panel-head"><div><h2>Recent activity</h2><p>Latest interactions with your card</p></div><a href="#">View all <i data-lucide="arrow-right"></i></a></div>
              <div class="activity-list"><div class="activity-row"><span class="activity-avatar lead">AR</span><div><b>Ashik Rahman shared his contact details</b><small>Lead capture · LinkedIn</small></div><time>2m ago</time></div><div class="activity-row"><span class="activity-avatar view"><i data-lucide="calendar-check"></i></span><div><b>Nusrat Tasnim booked an intro call</b><small>Tuesday, 2:30 PM</small></div><time>18m ago</time></div><div class="activity-row"><span class="activity-avatar">SM</span><div><b>Samiul M. saved your contact</b><small>Contact save · QR code</small></div><time>1h ago</time></div><div class="activity-row"><span class="activity-avatar view"><i data-lucide="mouse-pointer-2"></i></span><div><b>Your portfolio received 14 taps</b><small>Serein / Brand identity</small></div><time>3h ago</time></div></div>
            </section>
          </div>

          <div style="display:flex;flex-direction:column;gap:13px;min-width:0">
            <section class="panel completion-card">
              <div class="completion-top"><div><h2>Profile strength</h2><span style="font-size:8px;color:#849088">You're almost there</span></div><div class="progress-ring"><b>82%</b></div></div>
              <div class="checklist"><div class="check-row"><span class="done"><i data-lucide="check"></i></span>Basic information <a href="#">Edit</a></div><div class="check-row"><span class="done"><i data-lucide="check"></i></span>Contact details <a href="#">Edit</a></div><div class="check-row"><span class="done"><i data-lucide="check"></i></span>Social links <a href="#">Edit</a></div><div class="check-row"><span><i data-lucide="plus"></i></span>Add a testimonial <a href="#">Add</a></div></div>
            </section>

            <section class="panel card-preview-panel" id="card-preview">
              <div class="panel-head"><div><h2>Your live card</h2><p>Published · taply.me/himel</p></div><button data-drawer-open>Edit <i data-lucide="pencil"></i></button></div>
              <div class="desktop-card-preview"><div class="preview-card"><div class="preview-cover"></div><div class="preview-body"><img src="/images/himel.jpg" alt="Himel Ahmed"><h3>Himel Ahmed</h3><p>Brand Designer & Creative Director</p><div class="preview-actions"><span><i data-lucide="phone"></i></span><span><i data-lucide="message-circle"></i></span><span><i data-lucide="mail"></i></span><span><i data-lucide="globe-2"></i></span></div><button class="preview-save">SAVE CONTACT</button></div></div></div>
            </section>

            <section class="panel" id="appointments">
              <div class="panel-head"><div><h2>Upcoming</h2><p>Next appointments</p></div><a href="#">Calendar <i data-lucide="arrow-up-right"></i></a></div>
              <div class="activity-list"><div class="activity-row"><span class="activity-avatar" style="border-radius:5px;background:#f6e9c5;color:#806b30"><b>11</b></span><div><b>Intro call with Nusrat</b><small>Tuesday · 2:30 PM · Google Meet</small></div><time><i data-lucide="more-horizontal"></i></time></div><div class="activity-row"><span class="activity-avatar" style="border-radius:5px;background:#e7e0f6;color:#675687"><b>13</b></span><div><b>Brand review with Form Studio</b><small>Thursday · 10:00 AM · Zoom</small></div><time><i data-lucide="more-horizontal"></i></time></div></div>
            </section>
          </div>
        </div>
      </div>
    </main>
  </div>

  <nav class="app-mobile-nav" aria-label="Mobile navigation"><a class="active" href="/dashboard"><i data-lucide="layout-dashboard"></i>Overview</a><a href="#card-preview"><i data-lucide="contact-round"></i>Card</a><a href="#analytics"><i data-lucide="bar-chart-3"></i>Analytics</a><a href="#recent-activity"><i data-lucide="users"></i>Leads</a></nav>

  <div class="drawer-backdrop" id="drawer-backdrop"></div>
  <aside class="edit-drawer" id="edit-drawer" aria-label="Edit digital card">
    <div class="drawer-head"><div><h2>Edit your card</h2><span style="font-size:8px;color:#849088">Changes update your live card</span></div><button class="icon-button" id="drawer-close" aria-label="Close"><i data-lucide="x"></i></button></div>
    <form class="drawer-body" id="profile-form">
      <div class="form-field"><label class="form-label" for="profile-name">Full name</label><input id="profile-name" value="Himel Ahmed"></div>
      <div class="form-field"><label class="form-label" for="profile-role">Job title</label><input id="profile-role" value="Brand Designer & Creative Director"></div>
      <div class="form-field"><label class="form-label" for="profile-about">About you</label><textarea id="profile-about" rows="5">I turn ambitious ideas into clear, memorable brands. For the past eight years, I've partnered with founders and thoughtful teams.</textarea></div>
      <div class="form-field"><label class="form-label" for="profile-phone">Phone</label><input id="profile-phone" value="+880 1712 345 678"></div>
      <div class="form-field"><label class="form-label" for="profile-email">Email</label><input id="profile-email" type="email" value="hello@himel.studio"></div>
      <div class="form-field"><span class="form-label">Card theme</span><div class="theme-swatches"><button class="theme-swatch active" type="button" aria-label="Forest theme"><i></i></button><button class="theme-swatch" type="button" aria-label="Coral theme"><i></i></button><button class="theme-swatch" type="button" aria-label="Obsidian theme"><i></i></button><button class="theme-swatch" type="button" aria-label="Lilac theme"><i></i></button></div></div>
    </form>
    <div class="drawer-actions"><button class="button button-outline" id="drawer-cancel">Cancel</button><button class="button button-dark" id="save-profile" style="flex:1">Save changes <i data-lucide="check"></i></button></div>
  </aside>
  <div class="toast" id="toast"><i data-lucide="check-circle-2"></i><span>Changes saved</span></div>
</body>
</html>
