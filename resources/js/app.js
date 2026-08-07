import '../css/app.css';
import AOS from 'aos';
import { gsap } from 'gsap';
import { createIcons, icons } from 'lucide';

createIcons({ icons });

AOS.init({
  duration: 650,
  easing: 'ease-out-cubic',
  once: true,
  offset: 55,
});

const menuButton = document.querySelector('.menu-button');
const nav = document.querySelector('.nav-links');
menuButton?.addEventListener('click', () => {
  const isOpen = nav.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(isOpen));
  menuButton.innerHTML = `<i data-lucide="${isOpen ? 'x' : 'menu'}"></i>`;
  createIcons({ icons });
});

nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
  nav.classList.remove('open');
  menuButton?.setAttribute('aria-expanded', 'false');
}));

const header = document.querySelector('.site-header');
let sticky = false;
window.addEventListener('scroll', () => {
  const shouldStick = window.scrollY > 116;
  if (shouldStick !== sticky) {
    sticky = shouldStick;
    header?.classList.toggle('is-sticky', sticky);
    document.body.style.paddingTop = sticky ? `${header.offsetHeight}px` : '0';
  }
}, { passive: true });

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reducedMotion) {
  const heroTimeline = gsap.timeline({ defaults: { ease: 'power3.out' } });
  heroTimeline
    .from('.hero-eyebrow', { opacity: 0, y: 12, duration: .45 })
    .from('.hero h1', { opacity: 0, y: 24, duration: .7 }, '-=.2')
    .from('.hero-lead', { opacity: 0, y: 18, duration: .5 }, '-=.38')
    .from('.hero-actions, .trust-row', { opacity: 0, y: 14, duration: .45, stagger: .1 }, '-=.3')
    .from('.hero-phone', { opacity: 0, y: 35, rotate: 0, scale: .94, duration: .8 }, '-=.75')
    .from('.floating-note, .scan-card', { opacity: 0, scale: .82, duration: .45, stagger: .09 }, '-=.35');

  gsap.to('.hero-phone', { y: -8, duration: 2.8, repeat: -1, yoyo: true, ease: 'sine.inOut' });
  gsap.to('.note-views', { y: 7, rotate: -3, duration: 2.3, repeat: -1, yoyo: true, ease: 'sine.inOut' });
  gsap.to('.note-share', { y: -7, rotate: 1, duration: 2.6, repeat: -1, yoyo: true, ease: 'sine.inOut' });
}

document.querySelectorAll('[data-billing]').forEach((button) => {
  button.addEventListener('click', () => {
    document.querySelectorAll('[data-billing]').forEach((item) => item.classList.remove('active'));
    button.classList.add('active');
    const billing = button.dataset.billing;
    document.querySelectorAll('.price strong[data-monthly]').forEach((price) => {
      const nextValue = price.dataset[billing];
      if (reducedMotion) {
        price.textContent = nextValue;
      } else {
        gsap.to(price, {
          opacity: 0,
          y: -6,
          duration: .15,
          onComplete: () => {
            price.textContent = nextValue;
            gsap.fromTo(price, { y: 6 }, { opacity: 1, y: 0, duration: .2 });
          },
        });
      }
    });
  });
});

// Prevent empty showcase links from jumping unexpectedly.
document.querySelectorAll('a[href="#"]').forEach((link) => {
  link.addEventListener('click', (event) => event.preventDefault());
});
