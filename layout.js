/**
 * GlobalMed Entry Ltd - Centralized Site Layout Component
 * 
 * Provides a single source of truth for the Header, Mobile Drawer, and Footer.
 * Any change made here will automatically update across ALL site pages!
 */

(function () {
    // 0. Automatically clean URL in browser address bar (remove .html, show / for home)
    try {
        const rawPath = window.location.pathname;
        const search = window.location.search;
        const hash = window.location.hash;

        if (rawPath.endsWith('/index.html')) {
            let clean = rawPath.slice(0, -10);
            if (!clean) clean = '/';
            window.history.replaceState(null, '', clean + search + hash);
        } else if (rawPath === '/index.html' || rawPath === 'index.html') {
            window.history.replaceState(null, '', '/' + search + hash);
        } else if (rawPath.endsWith('.html')) {
            let clean = rawPath.slice(0, -5);
            window.history.replaceState(null, '', clean + search + hash);
        }
    } catch (e) {
        // Silently bypass in local file:// security sandboxes
    }

    // 1. Detect current page to automatically highlight the active nav item
    const currentPath = window.location.pathname.toLowerCase();
    
    function isActive(pageName) {
        if (pageName === 'home') {
            return currentPath.endsWith('index.html') || currentPath.endsWith('/') || currentPath === '' || currentPath.endsWith('/index');
        }
        if (pageName === 'about') {
            return currentPath.endsWith('about.html') || currentPath.endsWith('/about');
        }
        if (pageName === 'courses') {
            return currentPath.endsWith('courses.html') || currentPath.endsWith('/courses') || 
                   currentPath.endsWith('study-medicine-in-bulgaria.html') || currentPath.endsWith('/study-medicine-in-bulgaria') || 
                   currentPath.endsWith('study-medicine-in-bulgeria.html') || currentPath.endsWith('/study-medicine-in-bulgeria') || 
                   currentPath.endsWith('plovdiv-medical-university.html') || currentPath.endsWith('/plovdiv-medical-university') || 
                   currentPath.endsWith('university-details.html') || currentPath.endsWith('/university-details');
        }
        if (pageName === 'services') {
            return currentPath.endsWith('services.html') || currentPath.endsWith('/services');
        }
        if (pageName === 'process') {
            return currentPath.endsWith('application-process.html') || currentPath.endsWith('/application-process');
        }
        if (pageName === 'stories') {
            return currentPath.endsWith('success-stories.html') || currentPath.endsWith('/success-stories') || 
                   currentPath.endsWith('story-details.html') || currentPath.endsWith('/story-details');
        }
        if (pageName === 'resources') {
            return currentPath.endsWith('resources.html') || currentPath.endsWith('/resources') || 
                   currentPath.endsWith('resource-details.html') || currentPath.endsWith('/resource-details');
        }
        if (pageName === 'contact') {
            return currentPath.endsWith('contact.html') || currentPath.endsWith('/contact');
        }
        return false;
    }

    function navClass(pageName) {
        if (isActive(pageName)) {
            return 'text-primary font-bold relative py-1.5 after:content-[\'\'] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-[2px] after:bg-primary after:rounded-full whitespace-nowrap transition-colors';
        }
        return 'text-slate-600 hover:text-primary font-semibold py-1.5 relative whitespace-nowrap transition-colors';
    }

    function mobileNavClass(pageName) {
        if (isActive(pageName)) {
            return 'mobile-nav-link flex items-center justify-between px-3.5 py-2.5 rounded-[5px] bg-blue-50 text-primary font-bold text-sm transition-colors border border-blue-200 shadow-2xs';
        }
        return 'mobile-nav-link flex items-center justify-between px-3.5 py-2.5 rounded-[5px] text-slate-800 hover:bg-blue-50 hover:text-primary font-semibold text-sm transition-colors border border-transparent hover:border-blue-100';
    }

    // 2. Render Header HTML
    function getHeaderHTML() {
        return `
        <!-- Minimal Top Trust Bar -->
        <div class="hidden lg:block bg-slate-50/80 border-b border-slate-100 text-xs py-2 px-4 sm:px-6 lg:px-10">
            <div class="max-w-[1536px] mx-auto flex items-center justify-between text-slate-500 font-medium">
                <div class="flex items-center gap-6">
                    <a class="flex items-center gap-1.5 hover:text-primary transition-colors tracking-tight font-medium"
                        href="mailto:info@example.com">
                        <span class="material-symbols-outlined text-[15px] text-primary">mail</span>
                        <span>info@example.com</span>
                    </a>
                    <a class="flex items-center gap-1.5 hover:text-primary transition-colors tracking-tight font-medium"
                        href="tel:+447345692756">
                        <span class="material-symbols-outlined text-[15px] text-primary">call</span>
                        <span>+44 7345 692756</span>
                    </a>
                </div>
                <div class="flex items-center gap-6 text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-emerald-600">verified</span>
                        <span>UK Registered Company No. 10796193</span>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="flex items-center gap-1 font-semibold text-slate-800">
                        <span class="text-amber-500 font-bold">★ 4.9</span> on Trustpilot
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-700 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        UKMLA &amp; GMC Aligned
                    </span>
                </div>
            </div>
        </div>

        <!-- Executive Navbar Container with Fluid Max-Width & Proper Breathing Room -->
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-10 flex items-center justify-between h-16 sm:h-20">
            <!-- Brand Logo with Generous Right Margin -->
            <a class="flex items-center mr-4 lg:mr-8 2xl:mr-12 group shrink-0" href="index.html#home" aria-label="GlobalMed Entry Ltd Home">
                <img src="images/logo.webp" alt="GlobalMed Entry Ltd" class="h-10 sm:h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-102" />
            </a>

            <!-- 8 Canonical Desktop Navigation Links -->
            <nav class="hidden xl:flex items-center gap-5 2xl:gap-7 text-sm font-semibold text-slate-600">
                <a class="${navClass('home')}" href="index.html#home">Home</a>
                <a class="${navClass('about')}" href="about.html">About Us</a>
                <a class="${navClass('courses')}" href="courses.html">Courses &amp; Universities</a>
                <a class="${navClass('services')}" href="services.html">Our Services</a>
                <a class="${navClass('process')}" href="application-process.html">Application Process</a>
                <a class="${navClass('stories')}" href="success-stories.html">Success Stories</a>
                <a class="${navClass('resources')}" href="resources.html">Resources</a>
                <a class="${navClass('contact')}" href="contact.html">Contact</a>
            </nav>

            <!-- Right CTA Action & Mobile Hamburger Button -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                <a class="inline-flex items-center text-xs sm:text-sm font-semibold px-4 sm:px-5 py-2 sm:py-2.5 rounded-[5px] bg-primary text-white hover:bg-primary-dark transition-all shadow-sm shadow-blue-600/20 glow-badge-ring whitespace-nowrap active:scale-95"
                    href="contact.html#consultation-form">
                    Book Consultation
                </a>
                <button aria-controls="mobileMenuDrawer" aria-expanded="false" aria-label="Toggle navigation menu"
                    class="xl:hidden w-9 h-9 sm:w-10 sm:h-10 rounded-[5px] border border-slate-200 bg-white text-slate-700 hover:text-primary hover:border-slate-300 flex items-center justify-center transition-all focus:outline-none focus:ring-2 focus:ring-primary/20 shrink-0"
                    id="mobileMenuBtn" type="button">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">menu</span>
                </button>
            </div>
        </div>
        `;
    }

    // 3. Render Mobile Navigation Drawer HTML
    function getMobileDrawerHTML() {
        return `
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-300 opacity-0"
            id="mobileMenuBackdrop"></div>
        <!-- Slide-Out Panel -->
        <div class="fixed inset-y-0 right-0 w-full max-w-[320px] sm:max-w-sm h-full max-h-[100dvh] bg-white shadow-2xl flex flex-col justify-between transform translate-x-full transition-transform duration-300 ease-out z-10"
            id="mobileMenuPanel">
            <!-- Drawer Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
                <a href="index.html#home" class="flex items-center" aria-label="GlobalMed Entry Ltd Home">
                    <img src="images/logo.webp" alt="GlobalMed Entry Ltd" class="h-8 sm:h-9 w-auto object-contain" />
                </a>
                <button aria-label="Close navigation menu"
                    class="w-9 h-9 rounded-[5px] text-slate-500 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center transition-colors"
                    id="mobileMenuCloseBtn" type="button">
                    <span class="material-symbols-outlined text-[22px]">close</span>
                </button>
            </div>
            <!-- Navigation Links (Scrollable) -->
            <div class="px-4 sm:px-5 py-4 overflow-y-auto flex-1 min-h-0 space-y-1">
                <div class="text-xs font-bold uppercase tracking-widest text-slate-400 px-3 mb-2">Navigation Menu</div>
                <a class="${mobileNavClass('home')}" href="index.html#home">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">home</span>
                        Home
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('about')}" href="about.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">clinical_notes</span>
                        About Us
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('courses')}" href="courses.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">school</span>
                        Courses &amp; Universities
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('services')}" href="services.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">medical_services</span>
                        Our Services
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('process')}" href="application-process.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">alt_route</span>
                        Application Process
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('stories')}" href="success-stories.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">forum</span>
                        Success Stories
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('resources')}" href="resources.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">menu_book</span>
                        Resources
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
                <a class="${mobileNavClass('contact')}" href="contact.html">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] text-primary">support_agent</span>
                        Contact
                    </span>
                    <span class="material-symbols-outlined text-[15px] text-slate-400">chevron_right</span>
                </a>
            </div>
            <!-- Drawer Footer CTA -->
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/70 shrink-0 space-y-3">
                <a class="w-full inline-flex items-center justify-center text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-[5px] bg-primary text-white hover:bg-primary-dark transition-all shadow-sm shadow-blue-600/20 text-center"
                    href="contact.html#consultation-form">
                    Book Consultation Assessment
                </a>
                <div class="flex items-center justify-center gap-4 text-xs text-slate-500 font-medium">
                    <span class="flex items-center gap-1">
                        <span class="text-amber-500 font-bold">★</span> Trustpilot 4.9/5
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                        <span class="material-symbols-outlined text-[14px]">verified</span> GMC Aligned
                    </span>
                </div>
            </div>
        </div>
        `;
    }

    // 4. Render Global 5-Column Footer HTML
    function getFooterHTML() {
        return `
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 pb-10 sm:pb-12 border-b border-slate-100">
                <div class="lg:col-span-2 space-y-4">
                    <a href="index.html#home" class="inline-block group" aria-label="GlobalMed Entry Ltd Home">
                        <img src="images/logo.webp" alt="GlobalMed Entry Ltd" class="h-10 sm:h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-102" />
                    </a>
                    <p class="text-sm text-slate-500 leading-relaxed max-w-sm">
                        Founded by NHS and European clinical doctors, GlobalMed Entry Ltd is the UK&apos;s authoritative advisory firm for studying medicine and dentistry in accredited European universities.
                    </p>
                    <div class="flex items-center gap-4 text-xs font-semibold text-slate-700 pt-1">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">verified</span>
                            UK Reg. 10796193
                        </span>
                        <span>•</span>
                        <span>London, UK &amp; Sofia, Bulgaria</span>
                    </div>
                </div>

                <div>
                    <div class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-3">Partner Universities</div>
                    <ul class="space-y-2.5 text-sm">
                        <li><a class="hover:text-primary transition-colors" href="plovdiv-medical-university.html">Plovdiv Medical University</a></li>
                        <li><a class="hover:text-primary transition-colors" href="courses.html#universities">Sofia Medical University</a></li>
                        <li><a class="hover:text-primary transition-colors" href="courses.html#universities">Pleven Medical University</a></li>
                        <li><a class="hover:text-primary transition-colors" href="courses.html#universities">Varna Medical University</a></li>
                        <li><a class="hover:text-primary transition-colors" href="courses.html#universities">Trakia University</a></li>
                        <li><a class="hover:text-primary transition-colors" href="courses.html#universities">Prof. Dr. Assen Zlatarov</a></li>
                    </ul>
                </div>

                <div>
                    <div class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-3">Our Services</div>
                    <ul class="space-y-2.5 text-sm">
                        <li><a class="hover:text-primary transition-colors" href="services.html#phases">Phase I: Admissions Support</a></li>
                        <li><a class="hover:text-primary transition-colors" href="services.html#phases">Phase II: Relocation Support</a></li>
                        <li><a class="hover:text-primary transition-colors" href="services.html#phases">Phase III: Ongoing Support</a></li>
                        <li><a class="hover:text-primary transition-colors" href="services.html#phases">Phase IV: Alumni Support</a></li>
                        <li><a class="hover:text-primary transition-colors" href="services.html#packages">Our Premium Package</a></li>
                        <li><a class="hover:text-primary transition-colors" href="services.html#consultation">Free Consultation</a></li>
                    </ul>
                </div>

                <div>
                    <div class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-3">Company &amp; Governance</div>
                    <ul class="space-y-2.5 text-sm">
                        <li><a class="hover:text-primary transition-colors" href="about.html">About Us</a></li>
                        <li><a class="hover:text-primary transition-colors" href="success-stories.html">Success Stories</a></li>
                        <li><a class="hover:text-primary transition-colors" href="resources.html">Admissions Resources</a></li>
                        <li><a class="hover:text-primary transition-colors" href="about.html#leadership">Leadership Clinicians</a></li>
                        <li><a class="hover:text-primary transition-colors" href="about.html#accreditation">GMC &amp; HEE Relations</a></li>
                        <li><a class="hover:text-primary transition-colors" href="application-process.html">Application Process</a></li>
                        <li><a class="hover:text-primary transition-colors" href="contact.html">Contact &amp; Offices</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 sm:pt-8 flex flex-col sm:flex-row items-center justify-between text-sm text-slate-400 gap-4 text-center sm:text-left">
                <div>© 2025 GlobalMed Entry Ltd. Registered in England &amp; Wales (No. 10796193).</div>
                <div class="flex flex-wrap items-center justify-center sm:justify-end gap-4 sm:gap-6">
                    <a class="hover:text-slate-600 transition-colors" href="#">Privacy Policy</a>
                    <a class="hover:text-slate-600 transition-colors" href="#">Terms of Service</a>
                    <a class="hover:text-slate-600 transition-colors" href="#">GMC Compliance Notes</a>
                </div>
            </div>
        </div>
        `;
    }

    // 5. Injection & Mobile Drawer Interactive Event Binding
    function initLayout() {
        // Inject Header
        const headerEl = document.getElementById('site-header') || document.getElementById('siteHeader');
        if (headerEl) {
            headerEl.className = 'fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_2px_15px_-3px_rgba(15,23,42,0.04)]';
            headerEl.innerHTML = getHeaderHTML();
        }

        // Inject Mobile Drawer
        const drawerEl = document.getElementById('site-mobile-drawer') || document.getElementById('mobileDrawer') || document.getElementById('siteMobileDrawer');
        if (drawerEl) {
            drawerEl.className = 'fixed inset-0 z-[100] xl:hidden transition-all duration-300 pointer-events-none opacity-0 invisible';
            drawerEl.innerHTML = getMobileDrawerHTML();
        }

        // Inject Footer
        const footerEl = document.getElementById('site-footer') || document.getElementById('siteFooter');
        if (footerEl) {
            footerEl.className = 'bg-white border-t border-slate-200 pt-12 sm:pt-16 pb-8 text-slate-600 text-sm';
            footerEl.innerHTML = getFooterHTML();
        }

        // Bind Mobile Drawer Actions
        const drawer = drawerEl;
        const backdrop = document.getElementById('mobileMenuBackdrop');
        const panel = document.getElementById('mobileMenuPanel');
        const openBtn = document.getElementById('mobileMenuBtn');
        const closeBtn = document.getElementById('mobileMenuCloseBtn');
        const navLinks = drawer ? drawer.querySelectorAll('.mobile-nav-link') : [];

        function openMenu() {
            if (!drawer || !backdrop || !panel) return;
            drawer.classList.remove('invisible', 'pointer-events-none', 'opacity-0');
            drawer.classList.add('opacity-100');
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('translate-x-full');
            document.body.classList.add('overflow-hidden');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            if (!drawer || !backdrop || !panel) return;
            panel.classList.add('translate-x-full');
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            drawer.classList.remove('opacity-100');
            drawer.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
            setTimeout(() => {
                drawer.classList.add('invisible', 'pointer-events-none');
            }, 300);
        }

        if (openBtn) openBtn.addEventListener('click', openMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        if (backdrop) backdrop.addEventListener('click', closeMenu);

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                closeMenu();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && drawer && !drawer.classList.contains('invisible')) {
                closeMenu();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLayout);
    } else {
        initLayout();
    }
})();

