<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Connect with Himel Ahmed — Brand Designer and Creative Director in Dhaka." />
  <title>{{ $vcard->name }} — Digital Card</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/profile.js'])
</head>
<body class="profile-page">
  <header class="profile-topbar">
    <a class="brand" href="/"><span class="brand-mark">t</span>taply<span class="brand-dot">.</span></a>
    <div class="profile-top-actions">
      <a href="/" aria-label="Discover Taply"><i data-lucide="sparkles"></i><span>Made with Taply</span></a>
      <button type="button" data-open-modal="share-modal" aria-label="Share profile"><i data-lucide="share-2"></i><span>Share</span></button>
      <button type="button" data-open-modal="qr-modal" aria-label="Show QR code"><i data-lucide="qr-code"></i></button>
    </div>
  </header>

  <main class="public-card">
    <section class="public-cover">
      <div class="cover-status"><i></i> Available for select projects</div>
      <div class="public-cover-actions">
        <button type="button" data-open-modal="share-modal" aria-label="Share profile"><i data-lucide="share-2"></i></button>
        <button type="button" data-open-modal="qr-modal" aria-label="Show QR code"><i data-lucide="qr-code"></i></button>
      </div>
    </section>

    <div class="public-main">
      <div class="identity-row">
        <div>
          <img class="public-photo" src="/images/himel.jpg" alt="Portrait of {{ $vcard->name }}">
          <div class="identity-copy">
            <h1>{{ $vcard->name }} <i data-lucide="badge-check" aria-label="Verified"></i></h1>
            <p class="role">{{ $vcard->job_title }}</p>
            <div class="identity-meta"><span><i data-lucide="map-pin"></i> {{ $vcard->location }}</span><span><i data-lucide="clock-3"></i> 11:24 PM local time</span></div>
          </div>
        </div>
        <div class="profile-qr-mini" id="qr-mini" aria-label="QR code for this profile"></div>
      </div>

      <div class="profile-primary-actions">
        <button class="button button-dark" id="save-contact"><i data-lucide="user-plus"></i> Save to contacts</button>
        <button class="button button-outline" data-open-modal="booking-modal"><i data-lucide="calendar-days"></i> Book a meeting</button>
      </div>

      <section class="public-section">
        <div class="public-section-label">A LITTLE ABOUT ME</div>
        <p class="public-about">{{ $vcard->about }}</p>
      </section>

      <section class="public-section">
        <div class="public-section-label">LET'S CONNECT</div>
        <div class="contact-grid">
          <a class="contact-item" href="tel:+8801712345678"><span class="contact-icon"><i data-lucide="phone"></i></span><div><small>Call me</small><b>+880 1712 345 678</b></div></a>
          <a class="contact-item" href="https://wa.me/8801712345678" target="_blank" rel="noopener"><span class="contact-icon whatsapp"><i data-lucide="message-circle"></i></span><div><small>WhatsApp</small><b>Start a conversation</b></div></a>
          <a class="contact-item" href="mailto:hello@himel.studio"><span class="contact-icon"><i data-lucide="mail"></i></span><div><small>Email</small><b>hello@himel.studio</b></div></a>
          <a class="contact-item" href="https://himel.studio" target="_blank" rel="noopener"><span class="contact-icon"><i data-lucide="globe-2"></i></span><div><small>Website</small><b>himel.studio</b></div></a>
        </div>
      </section>

      <section class="public-section">
        <div class="public-section-label">FIND ME ONLINE</div>
        <div class="social-row">
          <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i data-lucide="facebook"></i></a>
          <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><i data-lucide="instagram"></i></a>
          <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i data-lucide="linkedin"></i></a>
          <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i data-lucide="youtube"></i></a>
          <a href="https://tiktok.com" target="_blank" rel="noopener" aria-label="TikTok"><i data-lucide="music-2"></i></a>
        </div>
      </section>

      <section class="public-section">
        <div class="public-section-label">WHAT I DO <span>03 services</span></div>
        <div class="service-list">
          <a class="service-item" href="mailto:hello@himel.studio?subject=Brand%20Identity"><span class="service-number">01</span><div><h3>Brand identity & systems</h3><p>Strategy, naming, visual identity, and practical guidelines.</p></div><i data-lucide="arrow-up-right"></i></a>
          <a class="service-item" href="mailto:hello@himel.studio?subject=Creative%20Direction"><span class="service-number">02</span><div><h3>Creative direction</h3><p>A clear creative vision for launches, campaigns, and teams.</p></div><i data-lucide="arrow-up-right"></i></a>
          <a class="service-item" href="mailto:hello@himel.studio?subject=Digital%20Design"><span class="service-number">03</span><div><h3>Digital design</h3><p>Thoughtful websites and product experiences that convert.</p></div><i data-lucide="arrow-up-right"></i></a>
        </div>
      </section>

      <section class="public-section">
        <div class="public-section-label">SELECTED WORK <span>View all <i data-lucide="arrow-right"></i></span></div>
        <div class="portfolio-grid">
          <a class="portfolio-card" href="#"><img src="/images/portfolio-brand.jpg" alt="Serein brand identity project"><span class="portfolio-overlay"><b>Serein / Brand identity</b><small>STRATEGY · IDENTITY · 2025</small></span></a>
          <a class="portfolio-card" href="#"><img src="/images/portfolio-editorial.jpg" alt="Kinfolk editorial art direction"><span class="portfolio-overlay"><b>Kinfolk / Editorial</b><small>ART DIRECTION · 2025</small></span></a>
          <a class="portfolio-card" href="#"><img src="/images/portfolio-packaging.jpg" alt="Aster product packaging"><span class="portfolio-overlay"><b>Aster / Packaging</b><small>BRAND · PACKAGING · 2024</small></span></a>
          <a class="portfolio-card" href="#"><img src="/images/portfolio-identity.jpg" alt="Form studio identity"><span class="portfolio-overlay"><b>Form / Digital</b><small>IDENTITY · DIGITAL · 2024</small></span></a>
        </div>
      </section>

      <section class="public-section">
        <div class="public-section-label">PAYMENT METHODS <span>Secure payments</span></div>
        <div class="payment-list">
          <button type="button" class="payment-item" data-payment="bKash"><span class="payment-logo bkash">bKash</span><small>Personal</small></button>
          <button type="button" class="payment-item" data-payment="Nagad"><span class="payment-logo nagad">Nagad</span><small>Personal</small></button>
          <button type="button" class="payment-item" data-payment="PayPal"><span class="payment-logo paypal">PayPal</span><small>International</small></button>
        </div>
      </section>

      <section class="book-banner">
        <div><h3>Have a project in mind?</h3><p>Book a free 20-minute intro call and tell me what you're building.</p></div>
        <button class="button button-accent" data-open-modal="booking-modal">Find a time <i data-lucide="arrow-right"></i></button>
      </section>

      <section class="public-section">
        <div class="public-section-label">RESUME & DOCUMENTS</div>
        <a href="/files/himel-ahmed-cv.pdf" download class="download-row" id="download-cv"><span class="download-file"><span class="download-icon"><i data-lucide="file-text"></i></span><span><b>Himel Ahmed — CV</b><small>PDF · Updated January 2026</small></span></span><i data-lucide="download"></i></a>
      </section>
    </div>

    <footer class="public-card-footer"><span>Last updated 3 days ago</span><a class="brand" href="/"><span class="brand-mark">t</span>taply<span class="brand-dot">.</span></a></footer>
  </main>

  <div class="modal-backdrop" id="share-modal" role="dialog" aria-modal="true" aria-labelledby="share-title">
    <div class="modal-panel"><div class="modal-head"><h3 id="share-title">Share Himel's card</h3><button class="icon-button" data-close-modal aria-label="Close"><i data-lucide="x"></i></button></div><div class="modal-body"><div class="share-options"><button class="share-option" data-share="facebook"><span><i data-lucide="facebook"></i></span>Facebook</button><button class="share-option" data-share="linkedin"><span><i data-lucide="linkedin"></i></span>LinkedIn</button><button class="share-option" data-share="whatsapp"><span><i data-lucide="message-circle"></i></span>WhatsApp</button><button class="share-option" data-share="email"><span><i data-lucide="mail"></i></span>Email</button></div><div class="copy-field"><input value="https://taply.me/himel" readonly aria-label="Profile URL"><button type="button" id="copy-url">Copy link</button></div></div></div>
  </div>

  <div class="modal-backdrop" id="qr-modal" role="dialog" aria-modal="true" aria-labelledby="qr-title">
    <div class="modal-panel"><div class="modal-head"><h3 id="qr-title">Scan to connect</h3><button class="icon-button" data-close-modal aria-label="Close"><i data-lucide="x"></i></button></div><div class="modal-body qr-modal-content"><div class="qr-large" id="qr-large"></div><p>Point your phone camera at the QR code to open Himel's digital card.</p><button class="button button-dark full" id="download-qr"><i data-lucide="download"></i> Download QR code</button></div></div>
  </div>

  <div class="modal-backdrop" id="booking-modal" role="dialog" aria-modal="true" aria-labelledby="booking-title">
    <div class="modal-panel"><div class="modal-head"><div><h3 id="booking-title">Book a conversation</h3></div><button class="icon-button" data-close-modal aria-label="Close"><i data-lucide="x"></i></button></div><form class="modal-body" id="booking-form"><div class="form-field"><span class="form-label">Choose a day</span><div class="date-pills"><button class="date-pill active" type="button">Mon 10</button><button class="date-pill" type="button">Tue 11</button><button class="date-pill" type="button">Wed 12</button><button class="date-pill" type="button">Thu 13</button></div></div><div class="form-field"><span class="form-label">Available times</span><div class="time-pills"><button class="time-pill active" type="button">10:00 AM</button><button class="time-pill" type="button">2:30 PM</button><button class="time-pill" type="button">5:00 PM</button></div></div><div class="form-field"><label class="form-label" for="booking-name">Your name</label><input id="booking-name" name="name" required placeholder="e.g. Sarah Ahmed"></div><div class="form-field"><label class="form-label" for="booking-email">Email address</label><input id="booking-email" type="email" name="email" required placeholder="you@company.com"></div><div class="form-field"><label class="form-label" for="booking-note">What would you like to discuss?</label><textarea id="booking-note" name="note" rows="3" placeholder="A few details about your project..."></textarea></div><button class="button button-accent full" type="submit">Confirm appointment <i data-lucide="arrow-right"></i></button></form></div>
  </div>

  <div class="modal-backdrop" id="payment-modal" role="dialog" aria-modal="true" aria-labelledby="payment-title">
    <div class="modal-panel"><div class="modal-head"><h3 id="payment-title">Pay with <span id="payment-name">bKash</span></h3><button class="icon-button" data-close-modal aria-label="Close"><i data-lucide="x"></i></button></div><div class="modal-body"><p style="font-size:11px;color:#68756f;line-height:1.6;margin-top:0">Send your payment to the verified account below, then include your reference in the message.</p><div style="border:1px solid #dde2de;padding:18px;margin:18px 0;display:flex;justify-content:space-between;align-items:center"><div style="display:flex;flex-direction:column;gap:4px"><small style="font-size:7px;color:#87928c;letter-spacing:.1em">ACCOUNT NUMBER</small><b style="font-size:17px">01712 345 678</b></div><button class="icon-button" id="copy-payment" aria-label="Copy account number"><i data-lucide="copy"></i></button></div><button class="button button-dark full" data-close-modal>Done</button></div></div>
  </div>

  <div class="toast" id="toast"><i data-lucide="check-circle-2"></i><span>Copied to clipboard</span></div>
</body>
</html>
