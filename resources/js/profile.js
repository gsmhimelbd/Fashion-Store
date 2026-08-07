import '../css/app.css';
import { gsap } from 'gsap';
import QRCode from 'qrcode';
import { createIcons, icons } from 'lucide';

createIcons({ icons });

const profileUrl = 'https://taply.me/himel';
const toast = document.getElementById('toast');
let toastTimer;

function showToast(message) {
  if (!toast) return;
  toast.querySelector('span').textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
}

async function copyText(value, message = 'Copied to clipboard') {
  try {
    await navigator.clipboard.writeText(value);
  } catch {
    const temporary = document.createElement('textarea');
    temporary.value = value;
    temporary.style.position = 'fixed';
    temporary.style.opacity = '0';
    document.body.appendChild(temporary);
    temporary.select();
    document.execCommand('copy');
    temporary.remove();
  }
  showToast(message);
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  requestAnimationFrame(() => modal.querySelector('[data-close-modal]')?.focus());
}

function closeModal(modal) {
  modal?.classList.remove('open');
  if (!document.querySelector('.modal-backdrop.open')) document.body.style.overflow = '';
}

document.querySelectorAll('[data-open-modal]').forEach((button) => {
  button.addEventListener('click', () => openModal(button.dataset.openModal));
});
document.querySelectorAll('[data-close-modal]').forEach((button) => {
  button.addEventListener('click', () => closeModal(button.closest('.modal-backdrop')));
});
document.querySelectorAll('.modal-backdrop').forEach((modal) => {
  modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(modal); });
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') closeModal(document.querySelector('.modal-backdrop.open'));
});

async function renderQR(target, size) {
  if (!target) return;
  const canvas = document.createElement('canvas');
  await QRCode.toCanvas(canvas, profileUrl, {
    width: size,
    margin: 1,
    color: { dark: '#16241e', light: '#fffef9' },
    errorCorrectionLevel: 'H',
  });
  canvas.style.width = '100%';
  canvas.style.height = '100%';
  target.replaceChildren(canvas);
}

renderQR(document.getElementById('qr-mini'), 120);
renderQR(document.getElementById('qr-large'), 360);

document.getElementById('download-qr')?.addEventListener('click', async () => {
  const dataUrl = await QRCode.toDataURL(profileUrl, { width: 1000, margin: 3, color: { dark: '#16241e', light: '#fffef9' } });
  const link = document.createElement('a');
  link.download = 'himel-ahmed-qr.png';
  link.href = dataUrl;
  link.click();
  showToast('QR code downloaded');
});

document.getElementById('copy-url')?.addEventListener('click', () => copyText(profileUrl, 'Profile link copied'));
document.getElementById('copy-payment')?.addEventListener('click', () => copyText('01712345678', 'Payment number copied'));

document.querySelectorAll('[data-share]').forEach((button) => {
  button.addEventListener('click', () => {
    const encodedUrl = encodeURIComponent(profileUrl);
    const text = encodeURIComponent("Connect with Himel Ahmed on Taply");
    const destinations = {
      facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
      linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
      whatsapp: `https://wa.me/?text=${text}%20${encodedUrl}`,
      email: `mailto:?subject=${text}&body=${encodedUrl}`,
    };
    window.open(destinations[button.dataset.share], '_blank', 'noopener,noreferrer,width=700,height=550');
  });
});

document.getElementById('save-contact')?.addEventListener('click', () => {
  const vcard = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    'FN:Himel Ahmed',
    'N:Ahmed;Himel;;;',
    'TITLE:Brand Designer & Creative Director',
    'TEL;TYPE=CELL:+8801712345678',
    'EMAIL:hello@himel.studio',
    'URL:https://himel.studio',
    'ADR;TYPE=WORK:;;Dhaka;;;Bangladesh',
    'END:VCARD',
  ].join('\r\n');
  const link = document.createElement('a');
  link.href = URL.createObjectURL(new Blob([vcard], { type: 'text/vcard' }));
  link.download = 'himel-ahmed.vcf';
  link.click();
  URL.revokeObjectURL(link.href);
  showToast('Contact card downloaded');
});

document.querySelectorAll('.date-pill, .time-pill').forEach((button) => {
  button.addEventListener('click', () => {
    button.parentElement.querySelectorAll('button').forEach((item) => item.classList.remove('active'));
    button.classList.add('active');
  });
});

document.getElementById('booking-form')?.addEventListener('submit', (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const name = new FormData(form).get('name') || 'there';
  const submit = form.querySelector('[type="submit"]');
  submit.disabled = true;
  submit.innerHTML = '<span class="spinner"></span> Confirming...';
  setTimeout(() => {
    closeModal(form.closest('.modal-backdrop'));
    form.reset();
    submit.disabled = false;
    submit.innerHTML = 'Confirm appointment <i data-lucide="arrow-right"></i>';
    createIcons({ icons });
    showToast(`Thanks ${String(name).split(' ')[0]} — your time is confirmed`);
  }, 700);
});

document.querySelectorAll('[data-payment]').forEach((button) => {
  button.addEventListener('click', () => {
    document.getElementById('payment-name').textContent = button.dataset.payment;
    openModal('payment-modal');
  });
});

document.getElementById('download-cv')?.addEventListener('click', () => showToast('CV download started'));

// Keep local time current for the public profile.
const clock = document.querySelector('.identity-meta span:nth-child(2)');
function updateClock() {
  if (!clock) return;
  const time = new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Dhaka', hour: 'numeric', minute: '2-digit' }).format(new Date());
  clock.innerHTML = `<i data-lucide="clock-3"></i> ${time} local time`;
  createIcons({ icons });
}
updateClock();
setInterval(updateClock, 60_000);

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  gsap.from('.public-card', { opacity: 0, y: 22, duration: .7, ease: 'power3.out' });
  gsap.from('.public-photo', { opacity: 0, scale: .84, duration: .55, delay: .3, ease: 'back.out(1.5)' });
  gsap.from('.identity-copy > *', { opacity: 0, y: 10, duration: .4, delay: .36, stagger: .07 });
}

document.querySelectorAll('a[href="#"]').forEach((link) => link.addEventListener('click', (event) => event.preventDefault()));
