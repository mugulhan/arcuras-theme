<?php
/**
 * Inline SVG Icons Helper
 *
 * Provides instant-loading inline SVG icons for better performance
 * Eliminates the flash of unstyled icons (FOUC) from Iconify
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get inline SVG icon
 *
 * @param string $icon Icon name (e.g., 'home', 'music', 'microphone')
 * @param string $class CSS classes to apply
 * @return string SVG markup
 */
function gufte_get_icon($icon, $class = '') {
    $icons = array(
        'home' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z"/></svg>',

        'music' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3v9.28a4.39 4.39 0 0 0-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21s4.5-2.01 4.5-4.5V6h4V3h-7z"/></svg>',

        'microphone' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3m7 9c0 3.53-2.61 6.44-6 6.93V21h-2v-3.07c-3.39-.49-6-3.4-6-6.93h2a5 5 0 0 0 5 5a5 5 0 0 0 5-5h2z"/></svg>',

        'microphone-variant' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3m7 9c0 3.53-2.61 6.44-6 6.93V21h-2v-3.07c-3.39-.49-6-3.4-6-6.93h2a5 5 0 0 0 5 5a5 5 0 0 0 5-5h2z"/></svg>',

        'account-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4m0 2a2 2 0 0 0-2 2a2 2 0 0 0 2 2a2 2 0 0 0 2-2a2 2 0 0 0-2-2m0 7c2.67 0 8 1.33 8 4v3H4v-3c0-2.67 5.33-4 8-4m0 1.9c-2.97 0-6.1 1.46-6.1 2.1v1.1h12.2V17c0-.64-3.13-2.1-6.1-2.1z"/></svg>',

        'calendar-range' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 10v2H7v-2h2m4 0v2h-2v-2h2m4 0v2h-2v-2h2m2-7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h1V1h2v2h8V1h2v2h1m0 16V8H5v11h14M9 14v2H7v-2h2m4 0v2h-2v-2h2m4 0v2h-2v-2h2z"/></svg>',

        'map-marker-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 2.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7m0 2a5 5 0 0 0-5 5c0 1 0 3 5 9.71C17 12 17 10 17 9a5 5 0 0 0-5-5z"/></svg>',

        'music-circle-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8s8 3.59 8 8s-3.59 8-8 8m1-13v6h3v2h-3v1a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2c.37 0 .7.11 1 .27V7h1z"/></svg>',

        'text-box-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2m0 2v14h14V5H5m2 4h10v2H7V9m0 4h10v2H7v-2z"/></svg>',

        'music-note-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3v10.55c-.59-.34-1.27-.55-2-.55a4 4 0 0 0-4 4a4 4 0 0 0 4 4a4 4 0 0 0 4-4V7h4V3h-6z"/></svg>',

        'soundcloud' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11.56 8.87V17h8.94c1.38 0 2.5-1.12 2.5-2.5s-1.12-2.5-2.5-2.5h-.5c-.46-2.37-2.51-4.15-5-4.15c-.95 0-1.85.27-2.63.73l-.31.18v.11M3.5 16.5l.79-4.62L3.5 7.23l-.79 4.65l.79 4.62m2.5.5l.82-5.12L5.99 6.25l-.81 5.63l.82 5.12m2.5.5l.84-5.62L8.5 5.88l-.84 5.62l.84 6m2.5.5l.86-6.12L10.5 5.5l-.86 6.38l.86 6.12z"/></svg>',

        'simple-icons:deezer' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18.81 6.75v1.5h3.93v-1.5h-3.93m0 2.25v1.5h3.93V9h-3.93m0 2.25v1.5h3.93v-1.5h-3.93m0 2.25v1.5h3.93v-1.5h-3.93m-4.95-9v1.5h3.93v-1.5h-3.93m0 2.25v1.5h3.93V9h-3.93m0 2.25v1.5h3.93v-1.5h-3.93m0 2.25v1.5h3.93v-1.5h-3.93m-4.95-4.5v1.5h3.93V9h-3.93m0 2.25v1.5h3.93v-1.5H8.91m0 2.25v1.5h3.93v-1.5H8.91m-4.95 0v1.5h3.93v-1.5H3.96m-2.7 2.25v1.5h3.93v-1.5H1.26z"/></svg>',

        'pen' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20.71 7.04c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.37-.39-1.02-.39-1.41 0l-1.84 1.83l3.75 3.75M3 17.25V21h3.75L17.81 9.93l-3.75-3.75L3 17.25z"/></svg>',

        'console' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7 8l-3.5 4L7 16v-3h5v-2H7V8m10 8l3.5-4L17 8v3h-5v2h5v3z"/></svg>',

        'console-line' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M13 19c0 .34.04.67.09 1H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8.09c-.33-.05-.66-.09-1-.09c-.34 0-.67.04-1 .09V6H5v12h8m-2-8l-4 4l4 4v-3h4v2h7v-2h-7v-2H11v-3m9 7h2v2h-2v-2m0 4h2v2h-2v-2z"/></svg>',

        'account-music-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11 14c0-3.36 2.64-6 6-6c2.2 0 4.08 1.13 5 3.09V6a2 2 0 0 0-2-2h-1V2h-2v2H9V2H7v2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4.81c-.51-.88-.81-1.9-.81-3c0-.34.03-.67.09-1H6V8h14v.81A6.995 6.995 0 0 0 11 14m8 0c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3m-1.5 4.5v-3l2.25 1.5l-2.25 1.5z"/></svg>',

        'account-edit-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M2 17v2h2l5.1-5.1l-2-2L2 17m14.5-9c.28 0 .5.22.5.5s-.22.5-.5.5s-.5-.22-.5-.5s.22-.5.5-.5m0-2C15.12 6 14 7.12 14 8.5c0 .74.33 1.41.83 1.87l-6.4 6.4l3 3l6.39-6.39c.45.49 1.11.81 1.85.81C21.88 14.19 23 13.07 23 11.69S21.88 9.19 20.5 9.19c-.73 0-1.39.32-1.84.81l-1.47-1.47c.45-.49.77-1.15.77-1.88C17.96 5.27 16.84 4.15 15.46 4.15m-2.96.35c-1.32 0-2.4 1.08-2.4 2.4s1.08 2.4 2.4 2.4c.28 0 .56-.05.81-.13l-1.79 1.79l1.42 1.42l1.79-1.79c-.09.25-.14.53-.14.81c0 1.32 1.08 2.4 2.4 2.4s2.4-1.08 2.4-2.4s-1.08-2.4-2.4-2.4c-.28 0-.56.05-.81.13l1.79-1.79l-1.42-1.42l-1.79 1.79c.09-.25.14-.53.14-.81c0-1.32-1.08-2.4-2.4-2.4M12.5 4c0-.27.1-.53.25-.71C11.5 3.1 10.13 3 9 3C5.69 3 3 5.69 3 9c0 2.5 1.5 4.67 3.64 5.64c.22-.07.46-.14.7-.14c1.31 0 2.41.83 2.83 2h.01c.1 0 .19.01.28.03C10.16 15.61 10 14.32 10 13c0-1.32.84-2.41 2-2.83V9c0-1.32 1.08-2.4 2.4-2.4c.28 0 .56.05.81.13l.79-.79c-.28-.38-.46-.84-.46-1.35c0-.28.05-.56.13-.81c-.72-.18-1.46-.28-2.23-.28C6.48 4 2 8.48 2 14s4.48 10 10 10s10-4.48 10-10c0-.77-.1-1.51-.28-2.23c-.25.09-.53.14-.81.14c-.51 0-.97-.18-1.35-.46l-.79.79c.09.25.14.53.14.81c0 1.32-1.08 2.4-2.4 2.4c-.28 0-.56-.05-.81-.13l.79-.79c.19.15.45.25.71.25c.66 0 1.2-.54 1.2-1.2s-.54-1.2-1.2-1.2s-1.2.54-1.2 1.2c0 .27.1.53.25.71l-.79.79c-.09-.25-.14-.53-.14-.81c0-1.32 1.08-2.4 2.4-2.4c.28 0 .56.05.81.13l-.79.79c-.19-.15-.45-.25-.71-.25c-.66 0-1.2.54-1.2 1.2s.54 1.2 1.2 1.2s1.2-.54 1.2-1.2m-10.31-9.48C3.95 5.88 4 6.43 4 7c0 3.04 1.69 5.67 4.19 7.03C7.45 13.38 7 12.24 7 11c0-2.76 2.24-5 5-5c1.24 0 2.38.45 3.27 1.19C16.33 4.69 18.96 3 22 3c.57 0 1.12.05 1.66.14C22.05 1.39 19.67 0 17 0C14.24 0 11.8 1.41 10.31 3.52z"/></svg>',

        'microphone-variant-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 2a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3m0 2a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1m6 6c0 2.21-1.79 4-4 4s-4-1.79-4-4H5c0 2.97 2.16 5.43 5 5.91V18H8v2h8v-2h-2v-2.09c2.84-.48 5-2.94 5-5.91h-2m-6 0V5h2v5h-2z"/></svg>',

        'trophy-variant' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4v2h16V2M5 7l.621 4.968c.239 1.917 1.452 3.577 3.17 4.34A6.009 6.009 0 0 0 11 17.92V20H6v2h12v-2h-5v-2.08a6.009 6.009 0 0 0 2.209-1.612c1.718-.763 2.931-2.423 3.17-4.34L19 7h-3V5H8v2H5m3 2h2v4.659a3.993 3.993 0 0 1-1.924-2.49L8 9m8 2.169L15.924 13.5A3.993 3.993 0 0 1 14 13.659V9h2v2.169Z"/></svg>',

        'close-circle-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2c5.53 0 10 4.47 10 10s-4.47 10-10 10S2 17.53 2 12S6.47 2 12 2m0 2a8 8 0 0 0-8 8a8 8 0 0 0 8 8a8 8 0 0 0 8-8a8 8 0 0 0-8-8m0 3.59l2.59 2.58l2.59-2.58L18.59 9l-2.58 2.59L18.59 14.41l-1.41 1.41l-2.59-2.58l-2.59 2.58L10.59 14.41l2.58-2.59L10.59 9L12 7.59z"/></svg>',

        'refresh' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M17.65 6.35A7.958 7.958 0 0 0 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0 1 12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>',

        'view-grid' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 11h8V3H3m0 18h8v-8H3m10 0h8V3h-8m0 18h8v-8h-8z"/></svg>',

        'view-list' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 5v2h12V5M9 11h12V9H9m0 6h12v-2H9m0 6h12v-2H9M5 9v2H3V9m0 6v2H3v-2m0 6v2H3v-2m0-12v2H3V5h2z"/></svg>',

        'album' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10s10-4.5 10-10S17.5 2 12 2m0 14c-2.2 0-4-1.8-4-4s1.8-4 4-4s4 1.8 4 4s-1.8 4-4 4m0-6c-1.1 0-2 .9-2 2s.9 2 2 2s2-.9 2-2s-.9-2-2-2z"/></svg>',

        'folder-music' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 6h-8l-2-2H4c-1.11 0-2 .89-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2m-2 10c0 1.1-.9 2-2 2s-2-.9-2-2s.9-2 2-2s2 .9 2 2m0-4h-2v-2l-3.5-3.5v3.5H11v2h2v2h2v-2h3v-2z"/></svg>',

        'chevron-down' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41 8.58L12 13.17l4.59-4.59L18 10l-6 6l-6-6l1.41-1.42z"/></svg>',

        'chevron-left' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15.41 16.58L10.83 12l4.58-4.59L14 6l-6 6l6 6l1.41-1.42z"/></svg>',

        'chevron-right' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M8.59 16.58L13.17 12L8.59 7.41L10 6l6 6l-6 6l-1.41-1.42z"/></svg>',

        'circle-medium' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle fill="currentColor" cx="12" cy="12" r="3"/></svg>',

        'dots-horizontal' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16 12a2 2 0 0 1 2-2a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2m-6 0a2 2 0 0 1 2-2a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2m-6 0a2 2 0 0 1 2-2a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2z"/></svg>',

        'dots-vertical' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 16a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2m0-6a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2m0-6a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2z"/></svg>',

        'download' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5 20h14v-2H5v2M19 9h-4V3H9v6H5l7 7l7-7z"/></svg>',

        'content-copy' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 21H8V7h11m0-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2m-3-4H4a2 2 0 0 0-2 2v14h2V3h12V1z"/></svg>',

        'thumb-up' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 22h9a2 2 0 0 0 2-2v-8c0-1.1-.9-2-2-2h-6.31l.95-4.57l.03-.32a1 1 0 0 0-.29-.7L12 2L6.59 7.41C6.22 7.78 6 8.3 6 8.83V20a2 2 0 0 0 2 2M4 10H2v12h2V10Z"/></svg>',

        'thumb-down' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15 2H6a2 2 0 0 0-2 2v8c0 1.1.9 2 2 2h6.31l-.95 4.57l-.03.32a1 1 0 0 0 .29.7L12 22l5.41-5.41c.37-.37.59-.89.59-1.42V4a2 2 0 0 0-2-2m7 0h-2v12h2V2Z"/></svg>',

        'clock' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10s10-4.5 10-10S17.5 2 12 2m4.2 14.2L11 13V7h1.5v5.2l4.5 2.7l-.8 1.3z"/></svg>',

        'star' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z"/></svg>',

        'history' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M13.5 8H12v5l4.28 2.54l.72-1.21l-3.5-2.08V8M13 3a9 9 0 0 0-9 9H1l3.96 4.03L9 12H6a7 7 0 0 1 7-7a7 7 0 0 1 7 7a7 7 0 0 1-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.896 8.896 0 0 0 13 21a9 9 0 0 0 9-9a9 9 0 0 0-9-9z"/></svg>',

        'account' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3s-3-1.34-3-3s1.34-3 3-3m0 14.2a7.2 7.2 0 0 1-6-3.22c.03-1.99 4-3.08 6-3.08c1.99 0 5.97 1.09 6 3.08a7.2 7.2 0 0 1-6 3.22z"/></svg>',

        'magnify' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9.5 3A6.5 6.5 0 0 1 16 9.5c0 1.61-.59 3.09-1.56 4.23l.27.27h.79l5 5l-1.5 1.5l-5-5v-.79l-.27-.27A6.516 6.516 0 0 1 9.5 16A6.5 6.5 0 0 1 3 9.5A6.5 6.5 0 0 1 9.5 3m0 2C7 5 5 7 5 9.5S7 14 9.5 14S14 12 14 9.5S12 5 9.5 5z"/></svg>',

        'arrow-right' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M4 11v2h12l-5.5 5.5l1.42 1.42L19.84 12l-7.92-7.92L10.5 5.5L16 11H4z"/></svg>',

        'menu' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 6h18v2H3V6m0 5h18v2H3v-2m0 5h18v2H3v-2z"/></svg>',

        'login' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M10 17v-3H3v-4h7V7l5 5l-5 5m0-15h9a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-9a2 2 0 0 1-2-2v-2h2v2h9V4h-9v2H8V4a2 2 0 0 1 2-2z"/></svg>',

        'logout' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16 17v-3H9v-4h7V7l5 5l-5 5M14 2a2 2 0 0 1 2 2v2h-2V4H5v16h9v-2h2v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9z"/></svg>',

        'music-note' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3v9.28a4.39 4.39 0 0 0-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21s4.5-2.01 4.5-4.5V6h4V3h-7z"/></svg>',

        'music-note-multiple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 6h12v2H3V6m0 4h12v2H3v-2m16-4v8.18A2.996 2.996 0 0 0 17 13c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3V8h3V6h-4z"/></svg>',

        'music-note-off' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28c-2.49 0-4.5 2.01-4.5 4.5S8.01 21 10.5 21s4.5-2.01 4.5-4.5V6h4V3h-7M2.81 2.81L1.39 4.22l13.5 13.5l1.41-1.41L2.81 2.81z"/></svg>',

        'filter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M14 12v7.88c.04.3-.06.62-.29.83a.996.996 0 0 1-1.41 0l-2.01-2.01a.989.989 0 0 1-.29-.83V12h-.03L4.21 4.62a1 1 0 0 1 .17-1.4c.19-.14.4-.22.62-.22h14c.22 0 .43.08.62.22a1 1 0 0 1 .17 1.4L14.03 12H14z"/></svg>',

        'music-note-plus' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M8 5.14v9.14c-.27-.09-.55-.14-.83-.14a2.5 2.5 0 0 0 0 5c1.38 0 2.5-1.12 2.5-2.5V8.14L15.5 10v3.14c-.27-.09-.55-.14-.83-.14a2.5 2.5 0 0 0 0 5c1.38 0 2.5-1.12 2.5-2.5V7.5l-9-2.36M11 3h2v3h3v2h-3v3h-2V8H8V6h3V3z"/></svg>',

        'account-edit' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M21.7 13.35l-1 1l-2.05-2.05l1-1c.21-.22.56-.22.77 0l1.28 1.28c.22.21.22.56 0 .77M12 18.94l6.06-6.06l2.05 2.05L14.06 21H12v-2.06M12 14c-4.42 0-8 1.79-8 4v2h6v-1.89l4-4c-.66-.08-1.32-.11-2-.11m0-10a4 4 0 0 0-4 4a4 4 0 0 0 4 4a4 4 0 0 0 4-4a4 4 0 0 0-4-4z"/></svg>',

        'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M13 3v6h8V3m-8 18h8V11h-8M3 21h8v-6H3m0-2h8V3H3v10z"/></svg>',

        'folder' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M10 4H4c-1.11 0-2 .89-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8l-2-2z"/></svg>',

        'file-document' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M13 9h5.5L13 3.5V9M6 2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.11.89-2 2-2m9 16v-2H6v2h9m3-4v-2H6v2h12z"/></svg>',

        'translate' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12.87 15.07l-2.54-2.51l.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35C8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5l3.11 3.11l.76-2.04M18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12m-2.62 7l1.62-4.33L19.12 17h-3.24z"/></svg>',

        'earth' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m-1 17.93c-3.95-.49-7-3.85-7-7.93c0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93m6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41c0 2.08-.8 3.97-2.1 5.39z"/></svg>',

        'play' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M8 5.14v14l11-7l-11-7z"/></svg>',

        'trophy' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4v2h16V2M5 7v6a5 5 0 0 0 5 5h2v3H6v2h12v-2h-6v-3h2a5 5 0 0 0 5-5V7h-3V5H8v2H5m2 2h2v6H8a3 3 0 0 1-1-.17V9m11 6a3 3 0 0 1-1 .17h-1V9h2v5.83M12 5h2v2h-2V5Z"/></svg>',

        'music-box' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-9 10c0 1.1-.9 2-2 2s-2-.9-2-2s.9-2 2-2s2 .9 2 2m0-7v5h5V6h-5z"/></svg>',

        'pencil' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20.71 7.04c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.37-.39-1.02-.39-1.41 0l-1.84 1.83l3.75 3.75M3 17.25V21h3.75L17.81 9.93l-3.75-3.75L3 17.25z"/></svg>',

        'calendar' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14v10M5 8V6h14v2H5m2 4h10v2H7v-2z"/></svg>',

        'calendar-blank' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14v10M5 8V6h14v2H5z"/></svg>',

        'calendar-music' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14v10M8 13h5v-1.5l-3-1v2.5c0 .83-.67 1.5-1.5 1.5S7 14.33 7 13.5S7.67 12 8.5 12c.17 0 .33.03.5.08V11l5 1.67V15c0 .83-.67 1.5-1.5 1.5S11 15.83 11 15s.67-1.5 1.5-1.5c.17 0 .33.03.5.08z"/></svg>',

        'calendar-star' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14v10m-7-8.66l.94 2.83l2.98.02l-2.41 1.75l.92 2.84L12 17.02l-2.43 1.76l.92-2.84l-2.41-1.75l2.98-.02L12 11.34z"/></svg>',

        'chevron-up' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6l-6 6l1.41 1.41z"/></svg>',

        'clock-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 20c4.4 0 8-3.6 8-8s-3.6-8-8-8s-8 3.6-8 8s3.6 8 8 8m0-18c5.5 0 10 4.5 10 10s-4.5 10-10 10S2 17.5 2 12S6.5 2 12 2m.5 5v5.2l4.5 2.7l-.8 1.3L11 13V7h1.5z"/></svg>',

        'close' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41z"/></svg>',

        'comment-text-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 22a1 1 0 0 1-1-1v-3H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6.1l-3.7 3.71c-.2.19-.45.29-.7.29H9m1-6v3.08L13.08 16H20V4H4v12h6M6 7h12v2H6V7m0 4h9v2H6v-2z"/></svg>',

        'heart' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5C2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',

        'help-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11 18h2v-2h-2v2m1-16C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8s8 3.59 8 8s-3.59 8-8 8m0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5c0-2.21-1.79-4-4-4z"/></svg>',

        'information' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M13 9h-2V7h2m0 10h-2v-6h2m-1-9A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2z"/></svg>',

        'information-outline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11 9h2V7h-2m1 13c-4.41 0-8-3.59-8-8s3.59-8 8-8s8 3.59 8 8s-3.59 8-8 8m0-18A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m-1 15h2v-6h-2v6z"/></svg>',

        'music-box-multiple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6m16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-8 10c0 1.1-.9 2-2 2s-2-.9-2-2s.9-2 2-2s2 .9 2 2m0-7v5h5V5h-5z"/></svg>',

        'music-note-off' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28c-2.49 0-4.5 2.01-4.5 4.5S8.01 21 10.5 21s4.5-2.01 4.5-4.5V6h4V3h-7M2.81 2.81L1.39 4.22l13.5 13.5l1.41-1.41L2.81 2.81z"/></svg>',

        'share-variant' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81c1.66 0 3-1.34 3-3s-1.34-3-3-3s-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65c0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>',

        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/></svg>',

        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M22.46 6c-.77.35-1.6.58-2.46.69c.88-.53 1.56-1.37 1.88-2.38c-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29c0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15c0 1.49.75 2.81 1.91 3.56c-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07a4.28 4.28 0 0 0 4 2.98a8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21C16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56c.84-.6 1.56-1.36 2.14-2.23Z"/></svg>',

        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91c0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23c-1.48 0-2.93-.39-4.19-1.15l-.3-.17l-3.12.82l.83-3.04l-.2-.32a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31c-.22.25-.87.86-.87 2.07c0 1.22.89 2.39 1 2.56c.14.17 1.76 2.67 4.25 3.73c.59.27 1.05.42 1.41.53c.59.19 1.13.16 1.56.1c.48-.07 1.46-.6 1.67-1.18c.21-.58.21-1.07.15-1.18c-.07-.1-.23-.16-.48-.27c-.25-.14-1.47-.74-1.69-.82c-.23-.08-.37-.12-.56.12c-.16.25-.64.81-.78.97c-.15.17-.29.19-.53.07c-.26-.13-1.06-.39-2-1.23c-.74-.66-1.23-1.47-1.38-1.72c-.12-.24-.01-.39.11-.5c.11-.11.27-.29.37-.44c.13-.14.17-.25.25-.41c.08-.17.04-.31-.02-.43c-.06-.11-.56-1.35-.77-1.84c-.2-.48-.4-.42-.56-.43c-.14 0-.3-.01-.47-.01Z"/></svg>',

        'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9.78 18.65l.28-4.23l7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3L3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>',

        'link-variant' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M10.59 13.41c.41.39.41 1.03 0 1.42c-.39.39-1.03.39-1.42 0a5.003 5.003 0 0 1 0-7.07l3.54-3.54a5.003 5.003 0 0 1 7.07 0a5.003 5.003 0 0 1 0 7.07l-1.49 1.49c.01-.82-.12-1.64-.4-2.42l.47-.48a2.982 2.982 0 0 0 0-4.24a2.982 2.982 0 0 0-4.24 0l-3.53 3.53a2.982 2.982 0 0 0 0 4.24m2.82-4.24c.39-.39 1.03-.39 1.42 0a5.003 5.003 0 0 1 0 7.07l-3.54 3.54a5.003 5.003 0 0 1-7.07 0a5.003 5.003 0 0 1 0-7.07l1.49-1.49c-.01.82.12 1.64.4 2.43l-.47.47a2.982 2.982 0 0 0 0 4.24a2.982 2.982 0 0 0 4.24 0l3.53-3.53a2.982 2.982 0 0 0 0-4.24a.973.973 0 0 1 0-1.42z"/></svg>',

        'spotify' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M17.9 10.9C14.7 9 9.35 8.8 6.3 9.75c-.5.15-1-.15-1.15-.6c-.15-.5.15-1 .6-1.15c3.55-1.05 9.4-.85 13.1 1.35c.45.25.6.85.35 1.3c-.25.35-.85.5-1.3.25m-.1 2.8c-.25.35-.7.5-1.05.25c-2.7-1.65-6.8-2.15-9.95-1.15c-.4.1-.85-.1-.95-.5c-.1-.4.1-.85.5-.95c3.65-1.1 8.15-.55 11.25 1.35c.3.15.45.65.2 1m-1.2 2.75c-.2.3-.55.4-.85.2c-2.35-1.45-5.3-1.75-8.8-.95c-.35.1-.65-.15-.75-.45c-.1-.35.15-.65.45-.75c3.8-.85 7.1-.5 9.7 1.1c.35.15.4.55.25.85M12 2A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2z"/></svg>',

        'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9c.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83c-.25.9-.83 1.48-1.73 1.73c-.47.13-1.33.22-2.65.28c-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44c-.9-.25-1.48-.83-1.73-1.73c-.13-.47-.22-1.1-.28-1.9c-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83c.25-.9.83-1.48 1.73-1.73c.47-.13 1.33-.22 2.65-.28c1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44c.9.25 1.48.83 1.73 1.73z"/></svg>',

        'apple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47c-1.34.03-1.77-.79-3.29-.79c-1.53 0-2 .77-3.27.82c-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51c1.28-.02 2.5.87 3.29.87c.78 0 2.26-1.07 3.81-.91c.65.03 2.47.26 3.64 1.98c-.09.06-2.17 1.28-2.15 3.81c.03 3.02 2.65 4.03 2.68 4.04c-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5c.13 1.17-.34 2.35-1.04 3.19c-.69.85-1.83 1.51-2.95 1.42c-.15-1.15.41-2.35 1.05-3.11z"/></svg>',

        'tag-multiple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5.5 9a1.5 1.5 0 0 0 1.5-1.5A1.5 1.5 0 0 0 5.5 6A1.5 1.5 0 0 0 4 7.5A1.5 1.5 0 0 0 5.5 9m12.5 6l-4.67-4.67C13.83 10.09 14 9.59 14 9c0-1.66-1.34-3-3-3S8 7.34 8 9s1.34 3 3 3c.59 0 1.09-.17 1.33-.46L17 16l1-1m.33-9L22 9.67l-9.33 9.33l-3.67-3.67L18.33 6z"/></svg>',

        'text-long' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 3v2h18V3M3 7h18v2H3m0 4h18v2H3m0 4h12v2H3v-2z"/></svg>',

        'trophy-award' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4v2h16V2M5 7v6a5 5 0 0 0 5 5h2v3H6v2h12v-2h-6v-3h2a5 5 0 0 0 5-5V7h-3V5H8v2H5m2 2h2v6H8a3 3 0 0 1-1-.17V9m11 6a3 3 0 0 1-1 .17h-1V9h2v5.83M12 5h2v2h-2V5m-6.5 6l1 1l1.5-1.5L9.5 12L8 10.5L6.5 12L5 10.5L6.5 9m11 1.5l-1.5 1.5l-1.5-1.5l-1.5 1.5l1.5 1.5l-1.5 1.5l1.5 1.5l1.5-1.5l1.5 1.5l1.5-1.5l-1.5-1.5l1.5-1.5l-1.5-1.5z"/></svg>',

        'trophy-variant' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4v2h16V2M5 7l.621 4.968c.239 1.917 1.452 3.577 3.17 4.34A6.009 6.009 0 0 0 11 17.92V20H6v2h12v-2h-5v-2.08a6.009 6.009 0 0 0 2.209-1.612c1.718-.763 2.931-2.423 3.17-4.34L19 7h-3V5H8v2H5m3 2h2v4.659a3.993 3.993 0 0 1-1.924-2.49L8 9m8 2.169L15.924 13.5A3.993 3.993 0 0 1 14 13.659V9h2v2.169Z"/></svg>',

        // Footer & Comments icons
        'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5l-8-5V6l8 5l8-5v2z"/></svg>',

        'arrow-right' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M4 11v2h12l-5.5 5.5l1.42 1.42L19.84 12l-7.92-7.92L10.5 5.5L16 11H4z"/></svg>',

        'arrow-left' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 11v2H8l5.5 5.5l-1.42 1.42L4.16 12l7.92-7.92L13.5 5.5L8 11h12z"/></svg>',

        'arrow-top-right' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5 5h14v14h-2V8.41L6.41 19L5 17.59L15.59 7H5V5z"/></svg>',

        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8A1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5a5 5 0 0 1-5 5a5 5 0 0 1-5-5a5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3z"/></svg>',

        'music' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M21 3v12.5a3.5 3.5 0 0 1-3.5 3.5a3.5 3.5 0 0 1-3.5-3.5a3.5 3.5 0 0 1 3.5-3.5c.54 0 1.05.12 1.5.34V6.47L9 7.6v8.9A3.5 3.5 0 0 1 5.5 20A3.5 3.5 0 0 1 2 16.5A3.5 3.5 0 0 1 5.5 13c.54 0 1.05.12 1.5.34V6l14-3z"/></svg>',

        'chevron-up' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6l-6 6l1.41 1.41z"/></svg>',

        'chevron-down' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6l-6-6l1.41-1.41z"/></svg>',

        'comment-multiple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3c5.5 0 10 3.58 10 8s-4.5 8-10 8c-1.24 0-2.43-.18-3.53-.5C5.55 21 2 21 2 21c2.33-2.33 2.7-3.9 2.75-4.5C3.05 15.07 2 13.13 2 11c0-4.42 4.5-8 10-8m5 9v-2h-2v2h2m-4 0v-2h-2v2h2m-4 0v-2H7v2h2z"/></svg>',

        'file-document-multiple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15 7h4V5l-4-4H9a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9h-6V7M3 19h16v2H3a2 2 0 0 1-2-2V5h2v14m6-14h5v5h5v10H9V5z"/></svg>',

        'play-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20m-1 6.5l6 3.5l-6 3.5v-7z"/></svg>',

        'lock' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5l-9-4m0 6c1.4 0 2.5 1.1 2.5 2.5v2c.8 0 1.5.7 1.5 1.5v4c0 .8-.7 1.5-1.5 1.5h-5c-.8 0-1.5-.7-1.5-1.5v-4c0-.8.7-1.5 1.5-1.5v-2C9.5 8.1 10.6 7 12 7m0 2c-.4 0-.5.1-.5.5V11h1V9.5c0-.4-.1-.5-.5-.5z"/></svg>',

        'content-save' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15 9H5V5h10m-3 14a3 3 0 0 1-3-3a3 3 0 0 1 3-3a3 3 0 0 1 3 3a3 3 0 0 1-3 3m5-16H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4z"/></svg>',

        'translate-off' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 5h-9.12L10 4.12V2H8v2.12L6.88 5H2v2h3.17l-1.9 5.17l1.94.66L6.3 10h1.91L9 10.79V13h2v-2.21l-.21-.19L12 7.17V5.88L13.12 7h8.79l1.8 4.94l-2.08.77l-1.29-3.52L17 12.86v.69L20.84 17l1.41-1.41L19.65 13h.7l.91-.25L23 17.29l-1.55 4.23l1.89.68L25.94 17l-2.19-6l-1.42-.38L22 9.72L20.05 5M12.71 15l-2.15 2.15l-.94-.28l1.04-2.84L12 12.71V15m2.79 2.05L11.84 21H8.77L6.61 16.93l1.18-3.24L4.87 10h5.44L11 10.69l-1.57 4.3L15 20.57l.5-.52"/></svg>',

        'google-translate' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 5h-9.12L10 4.12V2H8v2.12L6.88 5H2v2h3.17l.68 1.96l-1.93.64l-.36-1.06L2 10.5L4.5 18H7l1.5-4h3l1.5 4h2.5l-2.5-7.5l-.36 1.06l-1.93-.64L11.83 7H20v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a4 4 0 0 0 4-4V7a2 2 0 0 0-2-2M9.05 10.5L8 13.5l-1.05-3h2.1M18 14l-2-2v5h4l-2-3z"/></svg>',
    );

    if (!isset($icons[$icon])) {
        return '';
    }

    $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';

    // Add inline style for width and height to ensure proper sizing
    $svg = str_replace('<svg', '<svg' . $class_attr . ' style="width: 1em; height: 1em; display: inline-block; vertical-align: middle;"', $icons[$icon]);

    return $svg;
}

/**
 * Echo inline SVG icon
 */
function gufte_icon($icon, $class = '') {
    echo gufte_get_icon($icon, $class);
}
