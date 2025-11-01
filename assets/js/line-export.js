/**
 * Block-Based Lyric Line Export
 * Canvas-based image generation for individual lyric lines
 *
 * @package Gufte
 * @since 1.9.3
 */

(function() {
    'use strict';

    // Canvas instance (reusable)
    let canvas = null;

    // Prevent multiple initializations
    let initialized = false;

    // Prevent double downloads
    let isDownloading = false;

    /**
     * Initialize line export functionality
     */
    function initLineExport() {
        // Prevent double initialization
        if (initialized) {
            return;
        }

        const lyricsBlock = document.querySelector('.wp-block-arcuras-lyrics-translations');

        if (!lyricsBlock) {
            return;
        }

        initialized = true;

        // Add kebab menus to all lyric lines
        addKebabMenus();

        // Create canvas element
        createCanvas();

        // Initialize event listeners
        initEventListeners();
    }

    /**
     * Add kebab menu to each lyric line
     */
    function addKebabMenus() {
        const lyricsLines = document.querySelectorAll('.lyrics-line-group:not(.is-empty-line)');

        lyricsLines.forEach((lineGroup, index) => {
            // Check if kebab menu already exists
            if (lineGroup.querySelector('.line-kebab-menu')) {
                return;
            }

            // Make lineGroup position relative for absolute kebab menu
            lineGroup.style.position = 'relative';

            // Create kebab menu container
            const kebabContainer = document.createElement('div');
            kebabContainer.className = 'line-kebab-menu';
            kebabContainer.innerHTML = `
                <button class="kebab-trigger" data-line-index="${index}" aria-label="Line options">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </button>
                <div class="kebab-dropdown" id="dropdown-${index}">
                    <button class="dropdown-item save-line" data-line-index="${index}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Save Line</span>
                    </button>
                    <button class="dropdown-item export-line" data-line-index="${index}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export as Image</span>
                    </button>
                    <button class="dropdown-item copy-line" data-line-index="${index}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>Copy Text</span>
                    </button>
                </div>
            `;

            // Append to line-group (absolute positioned, outside text flow)
            lineGroup.appendChild(kebabContainer);
        });
    }

    /**
     * Create reusable canvas element
     */
    function createCanvas() {
        canvas = document.createElement('canvas');
        canvas.id = 'lyricLineCanvas';
        canvas.style.display = 'none';
        document.body.appendChild(canvas);
    }

    /**
     * Initialize all event listeners
     */
    function initEventListeners() {
        // Kebab menu toggles
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.kebab-trigger');

            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                toggleDropdown(trigger);
                return;
            }

            // Save button clicks
            const saveBtn = e.target.closest('.save-line');
            if (saveBtn) {
                e.preventDefault();
                handleSave(saveBtn.dataset.lineIndex);
                closeAllDropdowns();
                return;
            }

            // Export button clicks
            const exportBtn = e.target.closest('.export-line');
            if (exportBtn) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                handleExport(exportBtn.dataset.lineIndex);
                closeAllDropdowns();
                return;
            }

            // Copy button clicks
            const copyBtn = e.target.closest('.copy-line');
            if (copyBtn) {
                e.preventDefault();
                handleCopy(copyBtn.dataset.lineIndex);
                closeAllDropdowns();
                return;
            }

            // Close dropdowns when clicking outside
            if (!e.target.closest('.kebab-dropdown')) {
                closeAllDropdowns();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    }

    /**
     * Toggle dropdown visibility
     */
    function toggleDropdown(trigger) {
        const lineIndex = trigger.dataset.lineIndex;
        const dropdown = document.getElementById('dropdown-' + lineIndex);
        const isOpen = dropdown.classList.contains('active');

        // If already open, close it
        if (isOpen) {
            dropdown.classList.remove('active');
            trigger.classList.remove('active');
            return;
        }

        // Close all other dropdowns first
        closeAllDropdowns();

        // Then open this one
        dropdown.classList.add('active');
        trigger.classList.add('active');
    }

    /**
     * Close all open dropdowns
     */
    function closeAllDropdowns() {
        const dropdowns = document.querySelectorAll('.kebab-dropdown.active');
        const triggers = document.querySelectorAll('.kebab-trigger.active');

        dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
        triggers.forEach(trigger => trigger.classList.remove('active'));
    }

    /**
     * Handle line export to image
     */
    function handleExport(lineIndex) {
        // Prevent double downloads
        if (isDownloading) {
            return;
        }

        const lineGroup = document.querySelectorAll('.lyrics-line-group:not(.is-empty-line)')[lineIndex];

        if (!lineGroup) {
            return;
        }

        // Get texts
        const originalText = lineGroup.querySelector('.original-line .lyrics-line-text')?.textContent || '';
        const translationText = lineGroup.querySelector('.translation-line:not([style*="display: none"]) .lyrics-line-text')?.textContent || '';
        const activeLang = document.querySelector('.lang-button.active')?.textContent?.trim() || '';

        // Line number starts from 1 (not 0)
        const lineNumber = parseInt(lineIndex) + 1;

        // Set downloading flag
        isDownloading = true;

        // Generate image with line number for filename
        generateLineImage(originalText, translationText, activeLang, lineNumber);

        // Reset flag after a short delay
        setTimeout(() => {
            isDownloading = false;
        }, 1000);
    }

    /**
     * Handle copy to clipboard
     */
    function handleCopy(lineIndex) {
        const lineGroup = document.querySelectorAll('.lyrics-line-group:not(.is-empty-line)')[lineIndex];

        if (!lineGroup) {
            return;
        }

        const originalText = lineGroup.querySelector('.original-line .lyrics-line-text')?.textContent || '';
        const translationText = lineGroup.querySelector('.translation-line:not([style*="display: none"]) .lyrics-line-text')?.textContent || '';

        let textToCopy = originalText;
        if (translationText) {
            textToCopy += '\n' + translationText;
        }

        // Copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(textToCopy)
                .then(() => showNotification('Copied to clipboard!'))
                .catch(() => showNotification('Copy failed', 'error'));
        }
    }

    /**
     * Handle save line to user's collection
     */
    function handleSave(lineIndex) {
        // Check if user is logged in
        if (!arcurasLineExport?.isUserLoggedIn) {
            showNotification('Please log in to save lines', 'error');
            return;
        }

        const lineGroup = document.querySelectorAll('.lyrics-line-group:not(.is-empty-line)')[lineIndex];

        if (!lineGroup) {
            return;
        }

        // Get texts
        const originalText = lineGroup.querySelector('.original-line .lyrics-line-text')?.textContent || '';
        const translationText = lineGroup.querySelector('.translation-line:not([style*="display: none"]) .lyrics-line-text')?.textContent || '';

        // Get active language
        const activeLangButton = document.querySelector('.lang-button.active');
        const activeLangCode = activeLangButton?.getAttribute('data-lang') || '';
        const activeLangName = activeLangButton?.textContent?.trim().replace('Original', '').trim() || '';

        // Get post ID from body class
        const bodyClasses = document.body.className;
        const postIdMatch = bodyClasses.match(/postid-(\d+)/);

        if (!postIdMatch) {
            showNotification('Could not identify post', 'error');
            return;
        }

        const postId = postIdMatch[1];

        // Get post title
        const postTitle = arcurasLineExport?.postTitle || document.title;

        // Prepare data to save
        const lineData = {
            post_id: postId,
            post_title: postTitle,
            line_index: lineIndex,
            original_text: originalText,
            translation_text: translationText,
            language_code: activeLangCode,
            language_name: activeLangName,
            featured_image: arcurasLineExport?.featuredImage || ''
        };

        // Send to backend
        showNotification('Saving...', 'info');

        fetch('/wp-json/arcuras/v1/save-lyric-line', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': arcurasLineExport?.nonce || ''
            },
            body: JSON.stringify(lineData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Line saved successfully!', 'success');
            } else {
                showNotification(data.message || 'Save failed', 'error');
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            showNotification('Save failed. Please try again.', 'error');
        });
    }

    /**
     * Generate and download line image using canvas
     */
    function generateLineImage(originalText, translationText, language, lineNumber) {
        if (!canvas) {
            return;
        }

        const ctx = canvas.getContext('2d');

        // Set canvas dimensions (Instagram-friendly 4:5)
        const width = 1080;
        const height = 1350;
        canvas.width = width;
        canvas.height = height;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Draw background gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, '#667eea');
        gradient.addColorStop(1, '#764ba2');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);

        // Add top decorative line
        const lineGradient = ctx.createLinearGradient(0, 0, width, 0);
        lineGradient.addColorStop(0, 'rgba(255, 255, 255, 0.3)');
        lineGradient.addColorStop(0.5, 'rgba(255, 255, 255, 0.5)');
        lineGradient.addColorStop(1, 'rgba(255, 255, 255, 0.3)');
        ctx.fillStyle = lineGradient;
        ctx.fillRect(0, 0, width, 8);

        // Calculate vertical positions
        const topSectionY = 280; // Album cover and title area
        const centerY = height / 2 + 80; // Main text centered lower

        // Add album cover if available (top center, smaller)
        if (arcurasLineExport.featuredImage) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = arcurasLineExport.featuredImage;

            img.onload = function() {
                const coverSize = 180;
                const coverX = (width - coverSize) / 2;
                const coverY = topSectionY - 90;

                // Draw rounded cover
                ctx.save();
                roundRect(ctx, coverX, coverY, coverSize, coverSize, 12);
                ctx.clip();
                ctx.drawImage(img, coverX, coverY, coverSize, coverSize);
                ctx.restore();

                // Cover border
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
                ctx.lineWidth = 3;
                roundRect(ctx, coverX, coverY, coverSize, coverSize, 12);
                ctx.stroke();

                // Continue with rest of the drawing
                drawRestOfImage();
            };

            img.onerror = function() {
                // If image fails to load, continue without it
                drawRestOfImage();
            };
        } else {
            drawRestOfImage();
        }

        function drawRestOfImage() {
            // Add song title (below album cover with more spacing)
            if (arcurasLineExport.postTitle) {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.95)';
                ctx.font = '600 36px Inter, -apple-system, sans-serif';
                ctx.textAlign = 'center';

                // Wrap title if too long
                const maxTitleWidth = width - 200;
                const titleMetrics = ctx.measureText(arcurasLineExport.postTitle);

                if (titleMetrics.width > maxTitleWidth) {
                    wrapText(ctx, arcurasLineExport.postTitle, width / 2, topSectionY + 160, maxTitleWidth, 45);
                } else {
                    ctx.fillText(arcurasLineExport.postTitle, width / 2, topSectionY + 160);
                }
            }

            // Draw main text (centered in middle-lower area)
            ctx.fillStyle = '#ffffff';
            ctx.font = '700 58px Inter, -apple-system, sans-serif';
            ctx.textAlign = 'center';

            const mainLines = wrapText(ctx, originalText, width / 2, centerY - 50, width - 180, 75);

            // Draw translation if exists
            if (translationText) {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.75)';
                ctx.font = 'italic 400 42px Inter, -apple-system, sans-serif';
                wrapText(ctx, translationText, width / 2, centerY + 60 + (mainLines * 40), width - 180, 58);
            }

            // Add site branding at bottom
            ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
            ctx.font = '500 28px Inter, -apple-system, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('arcuras.com', width / 2, height - 60);

            // Add bottom decorative line
            ctx.fillStyle = lineGradient;
            ctx.fillRect(0, height - 8, width, 8);

            // Download image after everything is drawn
            setTimeout(() => {
                const link = document.createElement('a');

                // Create filename: songTitle-line-01.png
                let filename = 'lyrics';
                if (arcurasLineExport.postTitle) {
                    // Clean title for filename (remove special chars, limit length)
                    const cleanTitle = arcurasLineExport.postTitle
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .substring(0, 30);
                    filename = cleanTitle;
                }

                // Add line number with leading zero (01, 02, 03, etc.)
                if (lineNumber) {
                    const formattedNumber = lineNumber.toString().padStart(2, '0');
                    filename += '-line-' + formattedNumber;
                }

                filename += '.png';

                link.download = filename;
                link.href = canvas.toDataURL('image/png');
                link.click();

                showNotification('Image downloaded!');
            }, 100);
        }
    }

    /**
     * Wrap text to fit within maxWidth
     */
    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        const words = text.split(' ');
        let line = '';
        let lineCount = 0;

        for (let i = 0; i < words.length; i++) {
            const testLine = line + words[i] + ' ';
            const metrics = ctx.measureText(testLine);

            if (metrics.width > maxWidth && i > 0) {
                ctx.fillText(line, x, y + (lineCount * lineHeight));
                line = words[i] + ' ';
                lineCount++;
            } else {
                line = testLine;
            }
        }

        ctx.fillText(line, x, y + (lineCount * lineHeight));
        return lineCount + 1;
    }

    /**
     * Draw rounded rectangle
     */
    function roundRect(ctx, x, y, width, height, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
    }

    /**
     * Show notification to user
     */
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = 'line-export-notification ' + type;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLineExport);
    } else {
        initLineExport();
    }
})();
