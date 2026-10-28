# GlobalMed Entry Ltd Design System & Screenshot-to-Code Context Guide

> **Primary Directive for AI Agents & Developers**:  
> When the user provides a page screenshot or reference URL to build or update a page:
> 1. **Complete Content Extraction**: Extract and incorporate **100% of all content, text, data points, cards, FAQs, and structure** from the screenshot without omitting any detail.
> 2. **Strict Design System Fidelity**: Transform the visual look-and-feel so it strictly adheres to this **GlobalMed Entry Ltd Design System** rather than copying legacy or unpolished styling from the screenshot. The resulting page must look like a natural, cohesive extension of `index.html`, `about.html`, `courses.html`, `services.html`, `application-process.html`, and `contact.html`.

---

## 1. Core Visual Foundations

### A. Border Radius Rule
* **Standard Border Radius**: `rounded-[5px]` (5px) must be applied across **every single UI element**:
  * Buttons, inputs, textareas, select boxes
  * Cards, containers, modals, drawers, tooltips
  * Icon badge containers, pills, tags, chips
* **Strict Prohibition**: Never use default Tailwind `rounded-xl`, `rounded-2xl`, or `rounded-3xl` unless creating a perfectly circular avatar (`rounded-full`).

### B. Typography & Iconography
* **Font Families**:
  * **Headings & Titles**: `Rubik`, sans-serif (`font-heading`, `h1`-`h6`).
  * **Body Copy & Others**: `Karla`, sans-serif (`font-sans`, `font-body`).
* **Font Weights**:
  * Body: `font-normal` (400) or `font-medium` (500)
  * Subheadings / Actions: `font-semibold` (600) or `font-bold` (700)
  * Headings & Metrics: `font-extrabold` (800) or `font-black` (900)
* **Icon System**: Google `Material Symbols Outlined` (`material-symbols-outlined`) with consistent sizing (`text-[16px]` to `text-[26px]`).

### C. Color Palette

| Token | Hex / Class | Primary Use |
| :--- | :--- | :--- |
| **Primary Blue** | `#1D4ED8` (`bg-primary`, `text-primary`) | Primary brand color, primary CTAs, active states, key icons |
| **Primary Dark** | `#173FA8` / `#1E40AF` (`bg-primary-dark`) | Button hover states, emphasized accents |
| **Primary Light** | `#EFF6FF` (`bg-blue-50`) | Soft icon containers, light card backgrounds, active menu pills |
| **Page Canvas** | `#FCFDFE` (`bg-[#FCFDFE]`) | Global page background |
| **Surface Card** | `#FFFFFF` (`bg-white`) | Clean card containers, inputs, modals |
| **Surface Muted** | `#F8FAFD` (`bg-[#F8FAFD]`) | Alternating section backgrounds, form containers |
| **Border Clean** | `#E2E8F0` (`border-slate-200/90`) | Standard clean container dividers and borders |
| **Deep Executive Navy** | `#172554` / `#0F172A` | "Thinking About Applying This Year?", "Speak To An Expert", tool cards |
| **Amber / Gold Accent** | `#F59E0B` / `#EAB308` | Trustpilot stars, "FREE" ribbon badges, secondary high-converting CTAs |
| **Trustpilot Green** | `#00B67A` | Authentic Trustpilot star tiles, live active status indicators |
| **Headings & Titles** | `#192F59` (`text-slate-950`, `text-slate-900`) | Main headings, section titles, card headers |
| **Body & General Text** | `#3d4459` (`text-slate-600`, `text-slate-700`) | Body copy, explanatory descriptions, lists, general copy |
| **Text - Slate 400** | `#94A3B8` (`text-slate-400`) | Eyebrow badges, metadata tags, secondary timestamps |

---

## 2. Global Shell: Header & Navigation

Every page must contain the exact global header with synchronized desktop navigation and mobile drawer:

### A. Minimal Top Trust Bar (Hidden on Mobile, Visible on `lg:block`)
* Left: Email (`info@example.com`) and Phone (`+44 7345 692756`)
* Right:
  * `UK Registered Company No. 10796193` with green verified shield
  * `★ 4.9 on Trustpilot`
  * `UKMLA & GMC Aligned` with live green pulse dot

### B. Executive Navigation Bar (8 Canonical Links)
1. `Home` (`index.html#home`)
2. `About Us` (`about.html`)
3. `Courses & Universities` (`courses.html`)
4. `Our Services` (`services.html`)
5. `Application Process` (`application-process.html`)
6. `Success Stories` (`index.html#stories`)
7. `Resources` (`index.html#resources`)
8. `Contact` (`contact.html`)

* **Active State**: The current page must have `text-primary font-bold`, while others have `text-slate-600 hover:text-primary`.
* **Book Consultation Button**: `px-3 sm:px-5 py-2 sm:py-2.5 rounded-[5px] bg-primary text-white hover:bg-primary-dark`.
* **Mobile Drawer**: Slide-out drawer with close button, 8 items with right-chevrons, active highlight pill, and bottom consultation CTA.

---

## 3. Global Shell: 5-Column Executive Footer

Every page must conclude with the synchronized 5-column footer:
* **Column 1 & 2 (Brand Overview)**: Logo, Doctor-founded advisory firm summary, UK Company No. 10796193, London & Sofia offices.
* **Column 3 (Partner Universities)**: Plovdiv, Sofia, Pleven, Varna, Trakia, Prof. Dr. Assen Zlatarov.
* **Column 4 (Our Services)**: Phase I to IV Support, Our Premium Package, Free Consultation.
* **Column 5 (Company & Governance)**: About Us, Leadership Clinicians, Ethos, GMC Relations, Application Process, Contact & Offices.
* **Bottom Bar**: `© 2025 GlobalMed Entry Ltd. Registered in England & Wales (No. 10796193)` + Privacy Policy, Terms, GMC Compliance Notes.

---

## 4. Standard Reusable Component Patterns

### Pattern A: Hero Header Section
* **Background**: `bg-gradient-to-b from-[#EFF6FF]/70 via-[#F8FAFD] to-white border-b border-slate-200/80`
* **Mesh & Glow**:
  ```html
  <div class="absolute inset-0 bg-isometric-grid pointer-events-none opacity-40"></div>
  <div class="absolute top-1/4 -left-48 w-96 h-96 bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute top-1/3 -right-48 w-96 h-96 bg-indigo-300/20 rounded-full blur-3xl pointer-events-none"></div>
  ```
* **Eyebrow**:
  ```html
  <div class="inline-flex items-center gap-2 px-3 py-1 rounded-[5px] bg-blue-50 border border-blue-200/80 text-primary text-xs font-semibold uppercase tracking-wider shadow-xs">
      <span class="material-symbols-outlined text-[16px] text-primary">icon_name</span>
      Eyebrow Text
  </div>
  ```
* **Headline**: `text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-slate-950 leading-tight`
* **Lead Paragraph**: `text-sm sm:text-base md:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed`

---

### Pattern B: Executive "Speak To An Expert" CTA Card
Matches `services.html`, `application-process.html`, and `contact.html`:
```html
<div class="relative bg-gradient-to-br from-[#172554] via-[#1E3A8A] to-[#1E293B] text-white rounded-[5px] p-8 sm:p-10 text-center shadow-executive border border-blue-900/50 overflow-hidden">
    <!-- FREE Ribbon Badge -->
    <div class="absolute -top-1 -right-1">
        <div class="bg-amber-400 text-slate-950 text-[10px] font-black px-4 py-1 rotate-45 transform translate-x-3 translate-y-2 shadow-xs">
            FREE
        </div>
    </div>

    <div class="w-12 h-12 rounded-[5px] bg-white/10 text-white flex items-center justify-center mx-auto mb-4 border border-white/15 shadow-xs">
        <span class="material-symbols-outlined text-[26px]">support_agent</span>
    </div>

    <h3 class="text-2xl sm:text-3xl font-extrabold text-white mb-2 tracking-tight">
        Speak To An Expert
    </h3>
    <p class="text-xs sm:text-sm text-blue-100 font-normal mb-6 max-w-xs mx-auto leading-relaxed">
        Receive a FREE consultation with us and discuss your options.
    </p>
    <a href="#consultation"
        class="inline-block px-8 py-3 rounded-[5px] bg-white text-primary font-bold text-xs uppercase tracking-wider hover:bg-blue-50 transition-colors shadow-md">
        Click Here
    </a>
</div>
```

---

### Pattern C: "Thinking About Applying This Year?" Section
Placed prominently before the footer:
```html
<section class="py-16 sm:py-20 bg-[#172554] text-white text-center relative overflow-hidden" id="consultation">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 space-y-4 relative z-10">
        <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-[5px] bg-white/10 border border-white/20 text-white flex items-center justify-center shadow-md">
            <span class="material-symbols-outlined text-3xl sm:text-4xl">support_agent</span>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
            Thinking About Applying This Year?
        </h2>

        <p class="text-xs sm:text-sm text-blue-100 font-normal max-w-xl mx-auto leading-relaxed">
            Why not book a FREE consultation with an expert advisor.
        </p>

        <div class="pt-3">
            <a href="index.html#eligibility-widget"
                class="inline-block px-8 py-3.5 rounded-[5px] bg-white text-primary hover:bg-blue-50 font-bold text-xs uppercase tracking-wider transition-colors shadow-md">
                Book Now!
            </a>
        </div>
    </div>
</section>
```

---

### Pattern D: Authentic Trustpilot Verified Reviews Widget
```html
<div class="p-6 rounded-[5px] bg-white border border-slate-200/90 shadow-[0_4px_20px_-4px_rgba(15,23,42,0.06)] hover:shadow-executive hover:border-[#00B67A]/50 transition-all duration-300 group">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-1.5 mb-1">
                <div class="flex items-center gap-1">
                    <span class="w-6 h-6 rounded-[2px] bg-[#00B67A] text-white flex items-center justify-center text-xs font-bold leading-none">★</span>
                    <span class="w-6 h-6 rounded-[2px] bg-[#00B67A] text-white flex items-center justify-center text-xs font-bold leading-none">★</span>
                    <span class="w-6 h-6 rounded-[2px] bg-[#00B67A] text-white flex items-center justify-center text-xs font-bold leading-none">★</span>
                    <span class="w-6 h-6 rounded-[2px] bg-[#00B67A] text-white flex items-center justify-center text-xs font-bold leading-none">★</span>
                    <span class="w-6 h-6 rounded-[2px] bg-[#00B67A] text-white flex items-center justify-center text-xs font-bold leading-none">★</span>
                </div>
                <span class="text-sm font-extrabold text-slate-950 ml-1">Excellent</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                TrustScore <span class="font-bold text-slate-900">4.9</span> • <span class="font-semibold text-slate-700">500+ verified reviews</span>
            </p>
        </div>
        <span class="px-2.5 py-1 rounded-[5px] bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200/60 shadow-2xs shrink-0">
            Top 1% Rated
        </span>
    </div>
    <div class="pt-4 flex flex-wrap sm:flex-nowrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 text-[#00B67A]">
                <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span class="text-slate-950 font-black text-sm tracking-tight">Trustpilot</span>
            </div>
            <span class="text-slate-300">•</span>
            <span class="text-[11px] text-slate-500 font-medium">Verified Student Reviews</span>
        </div>
        <a href="https://www.trustpilot.com" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:text-primary-dark group-hover:underline transition-colors shrink-0">
            <span>Read reviews</span>
            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
        </a>
    </div>
</div>
```

---

### Pattern E: Interactive FAQ Accordion
```html
<div class="rounded-[5px] border border-slate-200 bg-white overflow-hidden shadow-xs">
    <button class="w-full flex items-center justify-between p-4 text-left font-bold text-xs sm:text-sm text-slate-800 hover:text-primary focus:outline-none"
        onclick="toggleFaq(1)" id="faq-btn-1">
        <span class="flex items-center gap-2.5">
            <span class="text-base text-slate-400 font-bold" id="faq-icon-1">+</span>
            <span>FAQ Question Title Here?</span>
        </span>
    </button>
    <div class="p-4 bg-slate-50/70 text-xs text-slate-600 leading-relaxed border-t border-slate-100 hidden" id="faq-content-1">
        Comprehensive explanatory answer goes here.
    </div>
</div>
```

---

## 5. Workflow Checklist for Every Screenshot-to-Page Task

When given a new screenshot or webpage reference:

1. **Extract 100% of the Content**:
   - Transcribe every headline, subtitle, paragraph, checklist, bullet point, table, testimonial, statistic, and CTA.
   - Do not summarize, shorten, or remove information unless requested.
2. **Apply GlobalMed Entry Design Tokens**:
   - All cards and inputs must use `rounded-[5px]`.
   - Never render raw default HTML forms or bare tables; wrap them in executive cards (`bg-white rounded-[5px] border border-slate-200/90 shadow-sm`).
   - Use the site's primary blue `#1D4ED8` and deep navy `#172554` palette.
3. **Embed Header & Footer**:
   - Include the global Top Trust Bar, 8-link desktop navigation, and mobile drawer.
   - Highlight the current page link.
   - Include the global 5-column footer.
4. **Link Cross-Navigation**:
   - Ensure the new page is added to the 8-link navigation on **all existing pages** (`index.html`, `about.html`, `courses.html`, `services.html`, `application-process.html`, `contact.html`).
5. **Quality Assurance**:
   - Run a Python `html.parser` check to confirm **0 unclosed or mismatched HTML tags**.
   - Verify all mobile drawer opening/closing JavaScript and FAQ toggles execute without errors.
