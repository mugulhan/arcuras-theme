/**
 * Music Video Modal
 * Handles popup functionality for music video embeds
 *
 * @package Arcuras
 * @since 2.2.1
 */

(function() {
    'use strict';

    /**
     * Initialize music video modal
     */
    function initMusicVideoModal() {
        const modal = document.getElementById('musicVideoModal');
        const trigger = document.querySelector('.music-video-trigger');
        const closeBtn = document.querySelector('.music-video-modal-close');
        const overlay = document.querySelector('.music-video-modal-overlay');

        console.log('Music Video Modal Init:', {
            modal: modal,
            trigger: trigger,
            closeBtn: closeBtn,
            overlay: overlay
        });

        if (!modal || !trigger) {
            console.warn('Music video modal or trigger not found');
            return;
        }

        // Open modal
        trigger.addEventListener('click', function() {
            console.log('Music video button clicked!');
            modal.classList.add('active');
            document.body.classList.add('music-video-modal-open');
        });

        // Close modal on close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        // Close modal on overlay click
        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        /**
         * Close modal and stop video
         */
        function closeModal() {
            modal.classList.remove('active');
            document.body.classList.remove('music-video-modal-open');

            // Stop video by reloading iframe
            const iframe = modal.querySelector('iframe');
            if (iframe) {
                const src = iframe.src;
                iframe.src = '';
                setTimeout(function() {
                    iframe.src = src;
                }, 100);
            }
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMusicVideoModal);
    } else {
        initMusicVideoModal();
    }
})();
