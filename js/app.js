/* ===== Jaffri Invoice Software Mirror - Shared JS ===== */

// ===== Session / Auth (localStorage based like the original demo) =====
const AUTH_KEY = 'erp_mirror_auth';

function isLoggedIn() {
  return localStorage.getItem(AUTH_KEY) === 'true';
}

function requireAuth() {
  if (window.location.pathname !== '/index.html' && window.location.pathname !== '/' && !isLoggedIn()) {
    window.location.href = 'index.html';
  }
}

function doLogin(email, password) {
  if (!email || !password) {
    return false;
  }

  localStorage.setItem(AUTH_KEY, 'true');
  localStorage.setItem('erp_user_email', email);
  window.location.hash = '#/';
  return true;
}

function doLogout() {
  localStorage.removeItem(AUTH_KEY);
  window.location.href = 'index.html';
}

// ===== Toast helper =====
function showToast(message, type = 'success') {
  const colors = { success: '#10b981', error: '#ef4444', info: '#3b82f6' };
  const toast = document.createElement('div');
  toast.style.cssText = `position:fixed;bottom:24px;right:24px;background:${colors[type]};color:#fff;padding:14px 24px;border-radius:12px;font-weight:600;font-size:.9rem;box-shadow:0 8px 20px rgba(0,0,0,.2);z-index:9999;animation:slideUp .3s ease;`;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.transition = 'opacity .4s';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 400);
  }, 2600);
}

// ===== Format helpers =====
function formatMoney(amount) {
  return 'Rs. ' + Number(amount || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ===== Sidebar behavior =====
document.addEventListener('DOMContentLoaded', function () {
  requireAuth();

  // Highlight active nav item based on hash
  const hash = window.location.hash || '#/';
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(function (item) {
    const href = item.getAttribute('href');
    if (href === hash) {
      item.classList.add('active');
    }
  });

  // Sidebar open/close (mobile)
  const menuBtn = document.querySelector('.menu-btn');
  const sidebar = document.querySelector('.sidebar');
  const closeBtn = document.querySelector('.sidebar-close-btn');
  const backdrop = document.querySelector('.sidebar-backdrop');
  const body = document.body;

  function openSidebar() {
    if (sidebar) {
      sidebar.classList.add('open');
      if (backdrop) backdrop.style.display = 'block';
    }
  }
  function closeSidebar() {
    if (sidebar) {
      sidebar.classList.remove('open');
      if (backdrop) backdrop.style.display = 'none';
    }
  }
  if (menuBtn) menuBtn.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (backdrop) backdrop.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebar();
  });

  // Log out button
  const logoutBtn = document.querySelector('#logoutBtn');
  if (logoutBtn) logoutBtn.addEventListener('click', doLogout);

  // Footer phone links
  const phoneLink = document.querySelector('#footerPhone');
  if (phoneLink) phoneLink.setAttribute('href', 'tel:+923352352689');

  // Avatar initial
  const avatars = document.querySelectorAll('.user-avatar');
  const email = localStorage.getItem('erp_user_email') || 'demo@jaffri-invoice.test';
  avatars.forEach(function (a) {
    a.textContent = email.charAt(0).toUpperCase();
  });
});

// ===== Render sidebar user info =====
function renderUserInfo() {
  const email = localStorage.getItem('erp_user_email') || 'demo@jaffri-invoice.test';
}

// ===== Modal helpers =====
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('show');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}
function closeAllModals() {
  document.querySelectorAll('.modal-overlay').forEach(function (m) {
    m.classList.remove('show');
  });
}
document.addEventListener('click', function (e) {
  if (e.target.classList && e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
  }
});

// ===== Icon SVG library (Lucide icons, matching original) =====
const Icons = {
  dashboard: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>',
  customers: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><path d="M16 3.128a4 4 0 0 1 0 7.744"></path><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle></svg>',
  vendors: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>',
  invoices: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" x2="8" y1="13" y2="13"></line><line x1="16" x2="8" y1="17" y2="17"></line></svg>',
  quotations: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>',
  purchaseOrders: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
  inventory: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>',
  cashbook: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>',
  generalLedger: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"></line><line x1="8" x2="21" y1="12" y2="12"></line><line x1="8" x2="21" y1="18" y2="18"></line><line x1="3" x2="3.01" y1="6" y2="6"></line><line x1="3" x2="3.01" y1="12" y2="12"></line><line x1="3" x2="3.01" y1="18" y2="18"></line></svg>',
  partyLedger: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>',
  reports: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M7 13v4"></path><path d="M11 9v8"></path><path d="M15 5v12"></path><path d="M19 9v8"></path></svg>',
  logout: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>',
  menu: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>',
  search: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>',
  bell: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>',
  plus: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>',
  download: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>',
  eye: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
  edit: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>',
  trash: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>',
  bank: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
  cash: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>',
  wallet: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path></svg>',
  x: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>',
  box: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>',
  file: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>',
  chart: '<svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M7 13v4"></path><path d="M11 9v8"></path><path d="M15 5v12"></path><path d="M19 9v8"></path></svg>',
};

// Inject icons wherever data-icon attribute is used
// PREPENDS the icon SVG so sibling text/spans are preserved.
function injectIcons(scope) {
  var root = scope || document;
  root.querySelectorAll('[data-icon]').forEach(function (el) {
    var name = el.getAttribute('data-icon');
    if (Icons[name] && !el.querySelector('svg')) {
      el.insertAdjacentHTML('afterbegin', Icons[name]);
    }
  });
}
document.addEventListener('DOMContentLoaded', function () {
  injectIcons(document);
});

// ===== Wipe Data =====
function wipeData() {
  // Matches original: confirm dialog, wipe + redirect to login
  var ok = confirm('Are you sure you want to completely wipe all demo data? This cannot be undone.');
  if (!ok) return;
  const keys = ['erp_customers', 'erp_vendors', 'erp_invoices', 'erp_quotations', 'erp_pos', 'erp_items', 'erp_entries', 'erp_banks'];
  keys.forEach(function (k) { localStorage.removeItem(k); });
  localStorage.removeItem(AUTH_KEY);
  window.location.href = 'index.html';
}

// ===== Layout renderer (sidebar + header + footer shared across pages) =====
// Multi-page mirror => nav links point to actual .html files
const NAV_ITEMS = [
  { href: 'dashboard.html', icon: 'dashboard', label: 'Dashboard', page: 'dashboard' },
  { href: 'customers.html', icon: 'customers', label: 'Customers', page: 'customers' },
  { href: 'vendors.html', icon: 'vendors', label: 'Vendors', page: 'vendors' },
  { href: 'invoices.html', icon: 'invoices', label: 'Invoices', page: 'invoices' },
  { href: 'quotations.html', icon: 'quotations', label: 'Quotations', page: 'quotations' },
  { href: 'purchase-orders.html', icon: 'purchaseOrders', label: 'Purchase Orders', page: 'purchase-orders' },
  { href: 'inventory.html', icon: 'inventory', label: 'Inventory', page: 'inventory' },
  { href: 'cashbook.html', icon: 'cashbook', label: 'Cash Book', page: 'cashbook' },
  { href: 'general-ledger.html', icon: 'generalLedger', label: 'General Ledger', page: 'general-ledger' },
  { href: 'party-ledger.html', icon: 'partyLedger', label: 'Party Ledger', page: 'party-ledger' },
  { href: 'reports.html', icon: 'reports', label: 'Reports', page: 'reports' },
];

function renderLayout(config) {
  // config: { title, subtitle, active }
  const email = localStorage.getItem('erp_user_email') || 'demo@jaffri-invoice.test';
  const activePage = config.active || 'dashboard';

  const navHtml = NAV_ITEMS.map(function (item) {
    const cls = item.page === activePage ? 'nav-item active' : 'nav-item';
    return '<a class="' + cls + '" href="' + item.href + '" data-icon="' + item.icon + '"><span>' + item.label + '</span></a>';
  }).join('');

  const html =
    '<aside class="sidebar">' +
    '  <button class="sidebar-close-btn" data-icon="x"></button>' +
    '  <div class="sidebar-header">' +
    '    <div class="logo-container"><img src="images/jaffri-invoice-logo-light.png" alt="Jaffri Invoice Software" style="max-width:100%;max-height:56px;display:block;margin:0 auto;"></div>' +
    '  </div>' +
    '  <nav class="sidebar-nav">' + navHtml + '</nav>' +
    '  <div class="sidebar-footer">' +
    '    <div class="user-info">' +
    '      <div class="user-avatar"></div>' +
    '      <div style="min-width:0">' +
    '        <div class="user-email">' + email + '</div>' +
    '        <div class="user-role">Administrator</div>' +
    '      </div>' +
    '    </div>' +
    '    <button class="sign-out-btn" id="logoutBtn" data-icon="logout"><span>Log Out</span></button>' +
    '  </div>' +
    '</aside>' +
    '<div class="sidebar-backdrop"></div>' +
    '<div class="main-content">' +
    '  <header class="header">' +
    '    <div class="header-left">' +
    '      <button class="menu-btn" data-icon="menu" aria-label="Menu"></button>' +
    '      <div class="header-title-container">' +
    '        <h2 class="header-title">' + config.title + '</h2>' +
    '        <p class="header-subtitle">' + (config.subtitle || 'Manage your project dashboard and performance') + '</p>' +
    '      </div>' +
    '    </div>' +
    '    <div class="header-right">' +
    '      <div class="search-bar">' +
    '        <span class="search-icon" data-icon="search"></span>' +
    '        <input type="text" placeholder="Search everything..." />' +
    '      </div>' +
    '      <button class="icon-btn wipe-data-btn" title="Completely wipe all demo data" onclick="wipeData()">Wipe Data</button>' +
    '      <button class="icon-btn" data-icon="bell" aria-label="Notifications"></button>' +
    '      <div class="header-divider"></div>' +
    '      <div class="header-profile"><div class="user-avatar-small">' + email.charAt(0).toUpperCase() + '</div></div>' +
    '    </div>' +
    '  </header>' +
    '  <div class="content" id="pageContent"></div>' +
    '  <footer class="app-footer">' +
    '    <span>Created By</span>' +
    '    <a class="footer-brand" href="https://wa.me/923352352689" target="_blank">Jaffri Invoice Software</a>' +
    '    <span class="footer-sep">·</span>' +
    '    <a class="footer-phone" href="tel:+923352352689">+92 335 2352689</a>' +
    '  </footer>' +
    '</div>';

  const layoutRoot = document.getElementById('app');
  if (layoutRoot) {
    layoutRoot.innerHTML = html;
    // re-inject icons (prepend so text spans stay)
    injectIcons(layoutRoot);
    // bind sidebar events
    const menuBtn = layoutRoot.querySelector('.menu-btn');
    const sidebar = layoutRoot.querySelector('.sidebar');
    const closeBtn = layoutRoot.querySelector('.sidebar-close-btn');
    const backdrop = layoutRoot.querySelector('.sidebar-backdrop');
    function openSidebar() { sidebar.classList.add('open'); backdrop.style.display = 'block'; }
    function closeSidebar() { sidebar.classList.remove('open'); backdrop.style.display = 'none'; }
    menuBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);
    layoutRoot.querySelector('#logoutBtn').addEventListener('click', doLogout);
  }
}

function renderLayoutWith(config, bodyHtml) {
  renderLayout(config);
  const content = document.getElementById('pageContent');
  if (content) content.innerHTML = bodyHtml;
}