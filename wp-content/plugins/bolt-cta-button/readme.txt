=== Bolt CTA Button ===
Contributors: ismeteroglu
Tags: call now button, whatsapp, sticky bar, floating button, click to call
Requires at least: 5.4
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A call now button bar & floating action button for WhatsApp, Phone, and more. Click analytics and full customization.

== Description ==

**Bolt CTA Button** is a powerful call now button plugin that adds sticky call-to-action buttons to your WordPress website. Choose between a **Sticky Bar** or a **Floating Action Button (FAB)** — or use different templates on mobile and desktop.

= Two Templates =

* **Sticky Bar** — Full-width bar at the top or bottom of the screen. 1-2 buttons: icon + text side by side. 3-5 buttons: icon on top, text below.
* **Floating Action Button (FAB)** — Round button at any corner of the screen. Click to reveal stacked sub-buttons with smooth animations.

= Key Features =

* **Up to 5 buttons** — WhatsApp, Phone, Telegram, Instagram, Email, Messenger, SMS, Location, Viber, or any custom link
* **Template selector** — Bar or FAB, with live preview in an iPhone mockup
* **Device-specific templates** — Use Bar on mobile and FAB on desktop, or vice versa
* **Drag & drop sorting** — Reorder buttons easily
* **Mobile & Desktop toggle** — Show/hide independently
* **Scroll behavior** — Always visible or hide when scrolling down
* **Timing triggers** — Show after X seconds delay and/or after scrolling X% of the page
* **6 bar animations** — Pulse, Glow, Bounce, Shake, Slide Up, or None
* **4 FAB animations** — Fan, Slide, Scale, Stagger
* **FAB customization** — Corner position, button size (48/56/64px), open direction (up/left/right), badge dot, tooltips
* **Full color control** — Background, text color, opacity, border radius, padding per button
* **Margin controls** — Custom margin/offset for both templates (top/right/bottom/left px)
* **Built-in SVG icons** — No external dependencies, lightweight
* **Click analytics** — Track total clicks, last 7 days, last 30 days per button with CSS bar chart
* **Page visibility rules** — All pages, only specific pages, or exclude specific pages
* **WooCommerce support** — Show different buttons on Shop, Product, Cart, and Checkout pages
* **Live preview** — Real-time iPhone 16 Pro mockup in admin panel
* **Lightweight** — Minimal CSS/JS, no jQuery on the frontend
* **Accessible** — ARIA labels, keyboard navigation, focus indicators, Escape to close FAB
* **Safe area support** — Works on notched devices (iPhone, etc.)
* **Translation ready** — Multiple languages included, admin language switcher

= Use Cases =

* Restaurant: WhatsApp ordering + phone reservations
* Service business: Click-to-call + Google Maps directions
* E-commerce: Customer support + WooCommerce-specific buttons
* Any business wanting easy mobile contact options

= How It Works =

1. Install and activate the plugin
2. Go to **Settings > Bolt CTA Button**
3. Choose your template (Bar or FAB)
4. Configure your buttons
5. Customize design, timing, and visibility
6. Save — done!

== Installation ==

1. Upload the `bolt-cta-button` folder to `/wp-content/plugins/`
2. Activate via **Plugins** menu
3. Navigate to **Settings > Bolt CTA Button**

== Frequently Asked Questions ==

= What is the difference between Bar and FAB templates? =

The **Bar** is a full-width strip at the top or bottom of the screen with buttons laid out horizontally. The **FAB** is a single round button at a screen corner that expands into stacked sub-buttons when clicked.

= Can I use different templates on mobile and desktop? =

Yes! In the Template & Design tab, you can set independent templates for mobile and desktop devices.

= Does it work with WooCommerce? =

Yes. You can enable WooCommerce rules to show specific buttons only on Shop, Product, Cart, or Checkout pages.

= How does click tracking work? =

The plugin tracks clicks per button using lightweight AJAX calls with `navigator.sendBeacon`. Data is stored in WordPress options (no custom tables). View statistics in the Analytics tab.

= Will it slow down my site? =

No. Frontend JS is vanilla (no jQuery), CSS is minimal, and assets are conditionally loaded (FAB CSS/JS only loads when FAB is active).

= Does the FAB support keyboard navigation? =

Yes. The FAB main button is a proper `<button>` element with `aria-expanded`. Pressing Escape closes the FAB. Sub-buttons have `role="menuitem"`.

== Screenshots ==

1. General & Buttons tab — enable/disable, mobile/desktop toggle, drag-and-drop button ordering with live iPhone preview
2. Template & Design tab — choose between Sticky Bar and Floating Action Button (FAB) with real-time preview
3. Display Rules tab — page visibility and timing triggers (delay, scroll percentage)
4. Analytics tab — per-button click statistics with 7-day and 30-day breakdowns

== Changelog ==

= 2.0.5 =
* NEW: "Help us improve" sidebar card with quick links to leave a review or request a feature in the support forum.

= 2.0.4 =
* CHANGE: Plugin renamed from "Bolt CTA Bar" to "Bolt CTA Button" to better reflect its floating button use case.
* REMOVED: Custom CSS field removed per WordPress.org plugin guidelines (no arbitrary code insertion).

= 2.0.3 =
* FIX: Admin language switcher now works correctly — translation files were not loading due to a filename mismatch.
* CHANGE: Plugin name references standardized across translations, code comments, and headers.

= 2.0.0 =
* NEW: Floating Action Button (FAB) template with 4 animations (Fan, Slide, Scale, Stagger)
* NEW: Template selector — switch between Bar and FAB
* NEW: Device-specific templates — different template per mobile/desktop
* NEW: FAB customization — corner position, size, open direction, badge, tooltips
* NEW: Timing triggers — show after delay and/or scroll percentage
* NEW: Click analytics — per-button click tracking with 7-day and 30-day stats
* NEW: WooCommerce conditional display — per-page button rules
* NEW: Margin/offset controls for both templates
* NEW: Tabbed admin settings panel
* IMPROVED: Admin preview with both Bar and FAB rendering

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.0.4 =
Plugin renamed to "Bolt CTA Button". Existing settings are preserved. Custom CSS field removed.

= 2.0.0 =
Major update: FAB template, click analytics, WooCommerce support, timing triggers, and much more. All v1.0 settings are preserved during upgrade.
