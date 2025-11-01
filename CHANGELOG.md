# Changelog

## [1.8.1] - 2025-10-15

### Added
- `Modified` column in the Posts list with human-readable timestamps and sortable header.

### Changed
- Minor admin styling tweaks so release and modified columns share consistent formatting.

## [1.8.0] - 2025-10-15

### Added
- Auto-detect translation languages when saving lyrics and pre-fill the sitemap language selection.

### Changed
- Translator role registration is idempotent and enforced on init/activation.

## [1.7.9] - 2025-10-14

### Changed
- Centered the search modal on all breakpoints and ensured it layers above the sticky header.
- Fully disabled background interaction when the modal is open, including mobile hero slider behaviour.
- Tweaked modal search field focus styling for a cleaner highlight.

## [1.7.8] - 2025-10-14

### Fixed
- Removed the duplicate skip-link output so only the screen-reader variant renders without creating visual gaps.
- Restored `wp-admin` styles by re-introducing the custom login hooks and cleaning up stray PHP close tags that were sending premature output.
- Brought the sticky site header back flush with the WordPress admin bar by dropping the hard-coded offsets.
- Forced music video embeds onto the `youtube-nocookie.com` player with lazy loading and stricter iframe attributes to reduce third-party cookies.
- Avoided preloading a non-existent `main-font.woff2` so browsers stop logging 404 errors.

## [1.7.7] - 2025-10-13

### Changed
- Added song release date Q&A to front-end FAQ blocks and schema structured data.
- Updated homepage quick links to surface Lyrics, Singers, Songwriters, and Albums.
- Pinned the header with sticky positioning so navigation remains visible while scrolling.

## [1.7.6] - 2025-10-13

### Changed
- Repacked the theme archive to include Tailwind `dist/` assets so front-end styles remain intact.
- Confirmed core sitemap output stays disabled and the generator Tools page loads via the corrected include path.

## [1.7.5] - 2025-10-13

### Changed
- Applied the `wp_sitemaps_enabled` filter globally so core never registers default sitemap endpoints when the theme is active.
- Added an upgrade-safe rewrite flush hook to refresh sitemap routes automatically after updates.
- Corrected the sitemap admin include path to ensure the generator screen renders in the Tools menu.

## [1.7.4] - 2025-10-13

### Changed
- Disabled WordPress core `wp-sitemap.xml`, routing all sitemap endpoints (`sitemap.xml`, `wp-sitemap.xml`, custom feeds) through the theme generator.
- Synced robots.txt and search engine pings to reference the canonical sitemap index served by the theme.
- Adjusted theme bootstrap to load the generator from `inc/utilities` and flush rewrite rules on theme switches for consistent sitemap availability.

## [1.7.3] - 2025-10-13

### Added
- Dedicated `gufte_feedback` custom post type with tailored admin columns and row actions for managing translation feedback entries.

### Changed
- Front-end translation menu now uses like/dislike controls with consistent icon alignment alongside download/copy actions.
- Feedback buttons only render for translated language blocks to avoid submitting feedback on the original lyrics.

## [1.7.2] - 2025-08-24

### Added
- Avatar preview for logged-in commenters and inline warning for links/inappropriate keywords.
- Backend validation that blocks comments containing URLs or adult/promotional keywords.
- Higher-contrast comment input styles for better readability.

## [1.7.1] - 2025-08-24

### Changed
- Fixed translated `<title>` rendering with single custom post types, ensured head output order.

## [1.7.0] - 2025-08-24

### Added
- AJAX-based translation switching via REST API, keeping `?lang=` URLs intact without full reloads.
- Dynamic title/meta localization for translated views, covering core and common SEO plugins.

### Changed
- Translation links now render localized `<title>` tags on the server while preserving default behavior for canonical pages.

## [1.6.1] - 2025-08-23

### Changed
- Header profile menu now routes to the front-end profile page (`/my-profile/`) instead of wp-admin.
- Refreshed resource hint configuration in tandem with jQuery migrate optimization.

## [1.6.0] - 2025-08-23

### Changed
- Tightened multilingual SEO workflow with validated language canonicals and hreflang fallbacks.
- Upgraded single post schema generation to guard against invalid locales and drop null JSON-LD values.
- Filtered FAQ markup icons to prevent JS errors and ensure accessible toggle interactions.

### Fixed
- Prevented language taxonomy names from leaking into `MusicRecording` genres.
- Ensured theme assets ship with the latest SEO meta, schema, and FAQ enhancements.

## [1.3.0] - 2025-10-11

### Added
- **Comprehensive Critical CSS System** (~8KB inline)
  - 100+ critical utility classes for instant rendering
  - Complete color palette (text, background, border colors)
  - Responsive utilities (`md:flex`, `md:block`)
  - Layout utilities (flexbox, grid, positioning)
  - Typography utilities (font sizes, weights, transforms)
  - Spacing utilities (margin, padding)
  - Border utilities (radius, width, colors)
  - Transition utilities for smooth animations
  - Group hover states for interactive elements
  - Container system with responsive breakpoints
- **Resource Optimization**
  - Preconnect hints for faster CDN connections
  - DNS prefetch for external resources
  - Preload strategy for non-critical CSS
  - Deferred loading for Swiper CSS and JS

### Changed
- **CSS Loading Strategy Optimized**
  - Theme stylesheet (style.css) now uses preload + async loading
  - Swiper CSS deferred with preload technique
  - Tailwind CSS with preconnect optimization (200-300ms faster)
- **Theme Description Updated**
  - Reflects performance optimizations and critical CSS implementation

### Fixed
- **Critical: Incognito Mode Layout Issues**
  - Fixed layout breaking in private/incognito mode
  - Critical CSS now prevents CLS (Cumulative Layout Shift)
  - Layout remains stable during CSS loading
- **Icon Color Rendering**
  - Fixed icons appearing black before Tailwind loads
  - All color classes now in critical CSS
  - Sidebar icon colors render correctly immediately
  - Button colors appear instantly
- **Render-Blocking Resources Eliminated**
  - Removed ~2,040ms of render-blocking time
  - Optimized CSS delivery reduces blocking by ~1,100ms
  - Non-critical resources load asynchronously

### Performance Improvements
- **Page Load Speed**
  - First Contentful Paint (FCP): Significantly improved
  - Largest Contentful Paint (LCP): Reduced by ~1 second
  - Time to Interactive (TTI): Faster page interactivity
  - Cumulative Layout Shift (CLS): Near-zero layout shifts
- **Resource Loading**
  - Tailwind CDN: 200-300ms faster with preconnect
  - Swiper CSS: Non-blocking with preload
  - Theme CSS: Deferred loading with critical inline
- **User Experience**
  - Instant above-the-fold rendering
  - Stable layout in all browsers (including incognito)
  - No color "flashing" or layout shifts
  - Smooth transitions and hover effects from first paint

### Technical Details
- Critical CSS size: ~8KB (minified inline)
- Total render-blocking reduction: ~2 seconds
- Zero FOUC (Flash of Unstyled Content)
- Zero FOIC (Flash of Invisible Content)
- Production-ready performance optimization

---

## [1.2.0] - 2025-10-11

### Added
- 26 new inline SVG icons to complete single page optimization:
  - `pencil`, `calendar`, `calendar-blank`, `calendar-music`, `calendar-star`
  - `chevron-up`, `clock-outline`, `close`, `comment-text-outline`
  - `heart`, `help-circle`, `information`, `information-outline`
  - `music-box-multiple`, `music-note-off`, `share-variant`
  - `spotify`, `youtube`, `apple` (platform icons)
  - `tag-multiple`, `text-long`, `trophy-award`, `trophy-variant`
  - `dots-vertical`, `download`, `content-copy`
- **Total icon library: 60+ optimized inline SVG icons**

### Changed
- **Complete Single Page Icon Optimization**: Replaced all Iconify icons with inline SVG
  - Single post pages (`templates/single.php`) - 55+ icon instances converted
  - Dynamic share buttons now use inline SVG
  - Award badges and result indicators optimized
  - FAQ accordion icons converted
- **Kebab Menu System**: Converted to inline SVG
  - Kebab menu trigger icon (`dots-vertical`)
  - Dropdown menu icons (`download`, `content-copy`)
  - Lyrics line actions now use inline SVG

### Fixed
- **Critical**: PHP syntax error on single pages causing white screen (line 130)
  - Fixed `the_title()` implementation with inline edit button
  - Properly separated title rendering from icon injection
- **Icon Sizing**: Added proper width/height to all SVG icons
  - Fixed oversized icons with `width: 1em; height: 1em` default sizing
  - Icons now properly scale with text and CSS classes
  - Vertical alignment corrected with `vertical-align: middle`
- **Kebab Menu**: Lyrics action menu icons now display correctly
  - Download lyrics as image feature restored
  - Copy lyrics to clipboard feature restored

### Performance Improvements
- Single pages now load significantly faster
- All icons render instantly without external dependencies
- Zero FOUC (Flash of Unstyled Content) across entire theme
- Complete elimination of Iconify CDN dependency

---

## [1.1.0] - 2025-10-11

### Added
- Inline SVG icon system (`/inc/utilities/inline-icons.php`)
  - 29+ optimized inline SVG icons
  - `gufte_icon()` and `gufte_get_icon()` helper functions
  - Support for custom CSS classes on icons
- New organized folder structure:
  - `/templates/` - WordPress core templates
  - `/templates/taxonomies/` - Taxonomy templates
  - `/page-templates/` - Page templates
  - `/inc/admin/` - Admin-related files
  - `/inc/features/` - Feature modules
  - `/inc/schema/` - Schema and structured data
  - `/inc/utilities/` - Helper utilities
  - `/build/` - Build configuration

### Changed
- **Performance Optimization**: Removed Iconify CDN dependency (~45KB)
- Replaced all Iconify icons with inline SVG across entire theme:
  - Sidebar (16 icons) - `template-parts/arcuras-sidebar.php`
  - Header (18 icons) - `templates/header.php`
  - Homepage (21 icons) - `templates/index.php`
- Updated theme description to reflect inline SVG optimization
- Reorganized file structure for better maintainability
- Updated all file paths in `functions.php` after reorganization

### Fixed
- Hero slider navigation button icons now display correctly
- FOUC (Flash of Unstyled Content) eliminated - icons load instantly
- Page template detection issue (symlinks added for WordPress compatibility)

### Performance Improvements
- Icons now load instantly without external JavaScript dependency
- Reduced page load time
- Eliminated render-blocking Iconify script
- Better perceived performance on all pages

---

## [1.0.0] - Initial Release

### Added
- Initial theme setup with Tailwind CSS
- Swiper JS for sliders
- Custom taxonomies (singers, songwriters)
- Credits system (Producer, Songwriter, Composer)
- Awards system
- Historical releases feature
- Multilingual lyrics support
- Responsive design
- Custom sidebar
- Schema markup support
