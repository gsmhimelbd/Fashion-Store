import '../css/app.css';
import QRCode from 'qrcode';
import { createIcons, icons } from 'lucide';

createIcons({ icons });

const toast = document.getElementById('toast');
let toastTimer;
function showToast(message) {
  if (!toast) return;
  toast.querySelector('span').textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2400);
}

const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('mobile-sidebar-toggle');
sidebarToggle?.addEventListener('click', () => sidebar?.classList.toggle('open'));
sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
  if (window.innerWidth <= 820) sidebar.classList.remove('open');
}));

document.addEventListener('click', (event) => {
  if (window.innerWidth <= 820 && sidebar?.classList.contains('open') && !sidebar.contains(event.target) && !sidebarToggle?.contains(event.target)) {
    sidebar.classList.remove('open');
  }
});

const drawer = document.getElementById('edit-drawer');
const backdrop = document.getElementById('drawer-backdrop');
function setDrawer(isOpen) {
  drawer?.classList.toggle('open', isOpen);
  backdrop?.classList.toggle('open', isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
}
document.querySelectorAll('[data-drawer-open]').forEach((button) => button.addEventListener('click', (event) => {
  if (button.matches('a')) event.preventDefault();
  setDrawer(true);
}));
document.getElementById('drawer-close')?.addEventListener('click', () => setDrawer(false));
document.getElementById('drawer-cancel')?.addEventListener('click', () => setDrawer(false));
backdrop?.addEventListener('click', () => setDrawer(false));

document.querySelectorAll('.theme-swatch').forEach((swatch) => {
  swatch.addEventListener('click', () => {
    document.querySelectorAll('.theme-swatch').forEach((item) => item.classList.remove('active'));
    swatch.classList.add('active');
    const color = getComputedStyle(swatch.querySelector('i')).backgroundColor;
    document.querySelector('.preview-cover')?.style.setProperty('background', color);
  });
});

document.getElementById('save-profile')?.addEventListener('click', () => {
  const name = document.getElementById('profile-name')?.value;
  const role = document.getElementById('profile-role')?.value;
  if (name) document.querySelector('.preview-body h3').textContent = name;
  if (role) document.querySelector('.preview-body p').textContent = role;
  setDrawer(false);
  showToast('Your card has been updated');
});

document.getElementById('dashboard-share')?.addEventListener('click', async () => {
  const data = { title: 'Himel Ahmed — Digital Card', text: 'Connect with me on Taply', url: 'https://taply.me/himel' };
  if (navigator.share) {
    try { await navigator.share(data); } catch { /* User dismissed the native share sheet. */ }
  } else {
    await navigator.clipboard.writeText(data.url);
    showToast('Card link copied');
  }
});

document.getElementById('dashboard-qr')?.addEventListener('click', async () => {
  const dataUrl = await QRCode.toDataURL('https://taply.me/himel', { width: 1000, margin: 3, color: { dark: '#16241e', light: '#fffef9' } });
  const link = document.createElement('a');
  link.download = 'himel-ahmed-qr.png';
  link.href = dataUrl;
  link.click();
  showToast('QR code downloaded');
});

document.querySelectorAll('.table-action').forEach((button) => {
  button.addEventListener('click', () => showToast(`${button.textContent.trim()} opened`));
});

document.querySelectorAll('a[href="#"]').forEach((link) => link.addEventListener('click', (event) => event.preventDefault()));
