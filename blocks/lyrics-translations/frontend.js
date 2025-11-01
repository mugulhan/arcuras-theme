/**
 * Lyrics & Translations Block - Frontend JavaScript
 */

(function() {
	'use strict';

	// Get language code from URL
	function getLangFromURL() {
		const path = window.location.pathname;
		const match = path.match(/\/(en|es|tr|fr|de|it|pt|ar|ja|ko)\/?$/);
		return match ? match[1] : null;
	}

	// Update URL when language changes
	function updateURL(langCode, isOriginal) {
		const currentPath = window.location.pathname;
		const langPattern = /\/(en|es|tr|fr|de|it|pt|ar|ja|ko)\/?$/;

		let newPath;

		if (isOriginal) {
			// Remove language code for original
			newPath = currentPath.replace(langPattern, '/');
		} else {
			if (langPattern.test(currentPath)) {
				// Replace existing language code
				newPath = currentPath.replace(langPattern, '/' + langCode + '/');
			} else {
				// Add language code
				newPath = currentPath.replace(/\/?$/, '/' + langCode + '/');
			}
		}

		// Update URL without page reload
		if (window.history && window.history.pushState) {
			window.history.pushState({lang: langCode}, '', newPath);
		}

		// Update page title and meta tags dynamically
		updatePageMeta(langCode, isOriginal);
	}

	// Update page title and meta tags via AJAX
	function updatePageMeta(langCode, isOriginal) {
		// Get post ID from body class or data attribute
		const bodyClasses = document.body.className;
		const postIdMatch = bodyClasses.match(/postid-(\d+)/);

		if (!postIdMatch) {
			return;
		}

		const postId = postIdMatch[1];
		const lang = isOriginal ? '' : langCode;

		// Make AJAX request to get updated meta
		const apiUrl = window.location.origin + '/wp-json/arcuras/v1/translation-meta?post_id=' + postId + '&lang=' + lang;

		fetch(apiUrl)
			.then(response => response.json())
			.then(data => {
				if (data.title) {
					// Update document title
					document.title = data.title;
				}

				if (data.description) {
					// Update meta description
					updateMetaTag('name', 'description', data.description);
				}

				if (data.og_title) {
					// Update OG tags
					updateMetaTag('property', 'og:title', data.og_title);
				}

				if (data.og_description) {
					updateMetaTag('property', 'og:description', data.og_description);
				}

				if (data.og_url) {
					updateMetaTag('property', 'og:url', data.og_url);
				}

				if (data.canonical) {
					// Update canonical link
					updateCanonicalLink(data.canonical);
				}
			})
			.catch(function(error) {
				// Silent fail - SEO tags will be updated on page reload
			});
	}

	// Initialize FAQ accordion functionality
	function initializeFAQ() {
		const faqButtons = document.querySelectorAll('[data-faq-toggle]');

		faqButtons.forEach(button => {
			// Remove existing listeners by cloning
			const newButton = button.cloneNode(true);
			button.parentNode.replaceChild(newButton, button);
		});

		// Re-query after cloning
		const newFaqButtons = document.querySelectorAll('[data-faq-toggle]');

		newFaqButtons.forEach(button => {
			button.addEventListener('click', function(e) {
				e.preventDefault();
				const targetId = this.getAttribute('data-faq-toggle');
				const targetElement = document.getElementById(targetId);
				const isExpanded = this.getAttribute('aria-expanded') === 'true';

				// Close all other FAQs
				newFaqButtons.forEach(otherButton => {
					if (otherButton !== this) {
						const otherTargetId = otherButton.getAttribute('data-faq-toggle');
						const otherTargetElement = document.getElementById(otherTargetId);

						otherButton.setAttribute('aria-expanded', 'false');
						if (otherTargetElement) {
							otherTargetElement.classList.add('hidden');
						}
					}
				});

				// Toggle current FAQ
				if (isExpanded) {
					this.setAttribute('aria-expanded', 'false');
					if (targetElement) {
						targetElement.classList.add('hidden');
					}
				} else {
					this.setAttribute('aria-expanded', 'true');
					if (targetElement) {
						targetElement.classList.remove('hidden');
					}
				}
			});
		});
	}

	// Make initializeFAQ globally available
	window.initializeFAQ = initializeFAQ;

	// Update FAQ section with new language
	function updateFAQLanguage(langCode, isOriginal) {
		const faqSection = document.querySelector('.auto-faq-section');
		if (!faqSection) {
			return; // No FAQ section on this page
		}

		// Get post ID from body class
		const bodyClasses = document.body.className;
		const postIdMatch = bodyClasses.match(/postid-(\d+)/);

		if (!postIdMatch) {
			return;
		}

		const postId = postIdMatch[1];
		const lang = isOriginal ? 'en' : langCode;

		// Make AJAX request to get updated FAQ HTML
		const apiUrl = window.location.origin + '/wp-json/arcuras/v1/faq-html?post_id=' + postId + '&lang=' + lang;

		fetch(apiUrl)
			.then(response => response.json())
			.then(data => {
				if (data.html) {
					// Replace FAQ section content
					const tempDiv = document.createElement('div');
					tempDiv.innerHTML = data.html;
					const newFaqSection = tempDiv.querySelector('.auto-faq-section');

					if (newFaqSection && faqSection.parentNode) {
						faqSection.parentNode.replaceChild(newFaqSection, faqSection);

						// Re-initialize FAQ accordion
						initializeFAQ();
					}
				}
			})
			.catch(function(error) {
				// Silent fail - FAQ will be updated on page reload
				console.log('FAQ update failed, please refresh the page');
			});
	}

	// Helper function to update meta tag
	function updateMetaTag(attribute, name, content) {
		let meta = document.querySelector('meta[' + attribute + '="' + name + '"]');

		if (meta) {
			meta.setAttribute('content', content);
		} else {
			// Create meta tag if it doesn't exist
			meta = document.createElement('meta');
			meta.setAttribute(attribute, name);
			meta.setAttribute('content', content);
			document.head.appendChild(meta);
		}
	}

	// Helper function to update canonical link
	function updateCanonicalLink(url) {
		let canonical = document.querySelector('link[rel="canonical"]');

		if (canonical) {
			canonical.setAttribute('href', url);
		} else {
			// Create canonical link if it doesn't exist
			canonical = document.createElement('link');
			canonical.setAttribute('rel', 'canonical');
			canonical.setAttribute('href', url);
			document.head.appendChild(canonical);
		}
	}

	// Initialize mobile language dropdown
	function initMobileLanguageDropdown(block) {
		const languageButtons = block.querySelector('.language-buttons');
		if (!languageButtons) return;

		// Check if dropdown already exists
		if (languageButtons.querySelector('.more-languages-dropdown')) return;

		const langButtons = languageButtons.querySelectorAll('.lang-button');
		if (langButtons.length <= 2) return; // No need for dropdown if 2 or fewer languages

		// Create dropdown container
		const dropdown = document.createElement('div');
		dropdown.className = 'more-languages-dropdown';

		// Create trigger button
		const trigger = document.createElement('button');
		trigger.className = 'more-languages-trigger';
		trigger.innerHTML = '+' + (langButtons.length - 2);
		trigger.setAttribute('aria-label', 'More languages');

		// Create dropdown menu
		const menu = document.createElement('div');
		menu.className = 'more-languages-menu';

		// Clone buttons starting from index 2 to menu
		Array.from(langButtons).slice(2).forEach(button => {
			const clonedButton = button.cloneNode(true);
			menu.appendChild(clonedButton);
		});

		dropdown.appendChild(trigger);
		dropdown.appendChild(menu);
		languageButtons.appendChild(dropdown);

		// Toggle dropdown on trigger click
		trigger.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			menu.classList.toggle('active');
		});

		// Close dropdown when clicking outside
		document.addEventListener('click', function(e) {
			if (!dropdown.contains(e.target)) {
				menu.classList.remove('active');
			}
		});

		// Handle clicks on dropdown menu items
		menu.addEventListener('click', function(e) {
			const button = e.target.closest('.lang-button');
			if (button) {
				menu.classList.remove('active');
			}
		});
	}

	// Get section comments from block attributes
	function getSectionComments(block) {
		try {
			const commentsData = block.getAttribute('data-section-comments');
			if (commentsData) {
				return JSON.parse(commentsData);
			}
		} catch (e) {
			console.error('Error parsing section comments:', e);
		}
		return {};
	}

	// Auto-detect sections from lyrics based on repetition
	function autoDetectSections(lyricsText) {
		if (!lyricsText) return [];

		const lines = lyricsText.split('\n');
		const blocks = [];
		let currentBlock = [];
		let lineIndex = 0;

		// Split into blocks separated by empty lines
		for (let i = 0; i < lines.length; i++) {
			if (lines[i].trim() === '') {
				if (currentBlock.length > 0) {
					blocks.push({
						lines: currentBlock,
						startIndex: lineIndex - currentBlock.length,
						normalized: currentBlock.join(' ').toLowerCase().trim()
					});
					currentBlock = [];
				}
			} else {
				currentBlock.push(lines[i]);
			}
			lineIndex++;
		}

		// Add last block
		if (currentBlock.length > 0) {
			blocks.push({
				lines: currentBlock,
				startIndex: lineIndex - currentBlock.length,
				normalized: currentBlock.join(' ').toLowerCase().trim()
			});
		}

		// Find repeating blocks
		const blockCounts = {};
		blocks.forEach((block, idx) => {
			const normalized = block.normalized;
			if (!blockCounts[normalized]) {
				blockCounts[normalized] = [];
			}
			blockCounts[normalized].push(idx);
		});

		// Assign section types
		const sections = [];
		let verseCounter = 1;
		const labeledNormalized = new Set();

		blocks.forEach((block, idx) => {
			const normalized = block.normalized;
			const occurrences = blockCounts[normalized].length;

			// If block appears more than once, it's a chorus
			if (occurrences >= 2) {
				if (!labeledNormalized.has(normalized)) {
					sections.push({
						name: 'Chorus',
						type: 'chorus',
						startIndex: block.startIndex,
						normalized: normalized
					});
					labeledNormalized.add(normalized);
				}
			} else {
				// Unique block, likely a verse
				sections.push({
					name: 'Verse ' + verseCounter,
					type: 'verse',
					startIndex: block.startIndex,
					normalized: normalized
				});
				verseCounter++;
			}
		});

		return sections;
	}

	// Detect manual section markers like [Verse 1], [Chorus]
	function detectManualSections(lyricsContainer) {
		const sectionPattern = /^\[(Verse \d+|Chorus|Bridge|Pre-Chorus|Intro|Outro|Hook|Interlude|Post-Chorus)\]/i;
		const sections = [];
		const lines = lyricsContainer.querySelectorAll('p, div');

		lines.forEach((line, index) => {
			const text = line.textContent.trim();
			const match = text.match(sectionPattern);
			if (match) {
				sections.push({
					name: match[1],
					element: line,
					index: index
				});
			}
		});

		return sections;
	}

	// Detect section headers - now they're rendered in HTML
	function detectSectionHeaders(block) {
		const lyricsContainer = block.querySelector('.lyrics-content-wrapper');
		if (!lyricsContainer) return [];

		// Find all section headers rendered by PHP
		const sectionHeaders = lyricsContainer.querySelectorAll('.lyrics-section-header');
		const sections = [];

		sectionHeaders.forEach(header => {
			const sectionName = header.getAttribute('data-section-name');
			const nameElement = header.querySelector('.section-name');

			if (sectionName && nameElement) {
				sections.push({
					name: sectionName,
					element: nameElement, // The section name span where we'll add the badge
					headerElement: header
				});
			}
		});

		// If no section headers found, try manual markers (backward compatibility)
		if (sections.length === 0) {
			const manualSections = detectManualSections(lyricsContainer);
			return manualSections;
		}

		return sections;
	}

	// Initialize section comments
	function initSectionComments(block) {
		const sectionComments = getSectionComments(block);
		if (!sectionComments || Object.keys(sectionComments).length === 0) {
			return; // No comments to display
		}

		const sections = detectSectionHeaders(block);
		if (!sections.length) {
			return; // No sections found
		}

		// Create modal container if it doesn't exist
		if (!document.getElementById('section-comment-modal')) {
			createCommentModal();
		}

		// Get current active language
		const getCurrentLang = function() {
			const activeButton = block.querySelector('.lang-button.active');
			return activeButton ? activeButton.getAttribute('data-lang') : 'en';
		};

		// Add comment badges to sections that have comments
		sections.forEach(section => {
			const langCode = getCurrentLang();
			const commentKey = section.name + '__' + langCode;

			// Check if this section has a comment for current language
			if (sectionComments[commentKey]) {
				// Add badge if not already added
				if (!section.element.querySelector('.section-comment-badge')) {
					const badge = document.createElement('span');
					badge.className = 'section-comment-badge';
					badge.innerHTML = '💬';
					badge.setAttribute('aria-label', 'View comment');
					badge.setAttribute('data-section', section.name);
					badge.setAttribute('data-lang', langCode);

					// Make section header's parent container position relative
					const lineGroup = section.element.closest('.lyrics-line-group');
					if (lineGroup) {
						lineGroup.style.position = 'relative';
					}
					section.element.style.position = 'relative';
					section.element.appendChild(badge);

					// Add click handler to badge only
					badge.addEventListener('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						const currentLang = getCurrentLang();
						openCommentModal(section.name, currentLang, sectionComments);
					});

					// Add visual feedback - cursor pointer on badge
					badge.style.cursor = 'pointer';
				}
			}
		});

		// Update badges when language changes
		const langButtons = block.querySelectorAll('.lang-button');
		langButtons.forEach(button => {
			button.addEventListener('click', function() {
				const newLang = this.getAttribute('data-lang');
				updateSectionBadges(block, sections, sectionComments, newLang);
			});
		});
	}

	// Update section badges when language changes
	function updateSectionBadges(block, sections, sectionComments, langCode) {
		sections.forEach(section => {
			const existingBadge = section.element.querySelector('.section-comment-badge');
			const commentKey = section.name + '__' + langCode;

			if (sectionComments[commentKey]) {
				// Add badge if it doesn't exist
				if (!existingBadge) {
					const badge = document.createElement('span');
					badge.className = 'section-comment-badge';
					badge.innerHTML = '💬';
					badge.setAttribute('aria-label', 'View comment');
					badge.setAttribute('data-section', section.name);
					badge.setAttribute('data-lang', langCode);

					// Make section header's parent container position relative
					const lineGroup = section.element.closest('.lyrics-line-group');
					if (lineGroup) {
						lineGroup.style.position = 'relative';
					}
					section.element.style.position = 'relative';
					section.element.appendChild(badge);

					// Add click handler to badge only
					badge.addEventListener('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						openCommentModal(section.name, langCode, sectionComments);
					});

					// Add visual feedback
					badge.style.cursor = 'pointer';
				} else {
					// Update existing badge
					existingBadge.setAttribute('data-lang', langCode);
				}
			} else {
				// Remove badge if no comment for this language
				if (existingBadge) {
					existingBadge.remove();
				}
			}
		});
	}

	// Create comment modal
	function createCommentModal() {
		const modal = document.createElement('div');
		modal.id = 'section-comment-modal';
		modal.className = 'section-comment-modal';
		modal.innerHTML = `
			<div class="section-comment-overlay"></div>
			<div class="section-comment-content">
				<div class="section-comment-header">
					<h3 class="section-comment-title"></h3>
					<button class="section-comment-close" aria-label="Close">×</button>
				</div>
				<div class="section-comment-body"></div>
			</div>
		`;

		document.body.appendChild(modal);

		// Close modal on overlay click
		modal.querySelector('.section-comment-overlay').addEventListener('click', closeCommentModal);

		// Close modal on close button click
		modal.querySelector('.section-comment-close').addEventListener('click', closeCommentModal);

		// Close modal on ESC key
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal.classList.contains('active')) {
				closeCommentModal();
			}
		});
	}

	// Open comment modal
	function openCommentModal(sectionName, langCode, sectionComments) {
		const modal = document.getElementById('section-comment-modal');
		if (!modal) return;

		const commentKey = sectionName + '__' + langCode;
		const comment = sectionComments[commentKey];

		if (!comment) return;

		// Update modal content
		modal.querySelector('.section-comment-title').textContent = sectionName;
		modal.querySelector('.section-comment-body').textContent = comment;

		// Show modal
		modal.classList.add('active');
		document.body.style.overflow = 'hidden';
	}

	// Close comment modal
	function closeCommentModal() {
		const modal = document.getElementById('section-comment-modal');
		if (!modal) return;

		modal.classList.remove('active');
		document.body.style.overflow = '';
	}

	// Initialize all lyrics blocks on the page
	function initLyricsBlocks() {
		const blocks = document.querySelectorAll('.lyrics-block-frontend, .wp-block-arcuras-lyrics-translations');

		blocks.forEach(block => {
			// Initialize mobile dropdown
			initMobileLanguageDropdown(block);

			// Initialize section comments
			initSectionComments(block);

			const langButtons = block.querySelectorAll('.lang-button');
			const translationLines = block.querySelectorAll('.translation-line');
			const statsContainers = block.querySelectorAll('.stats-container');

			if (!langButtons.length) return;

			langButtons.forEach(button => {
				button.addEventListener('click', function(e) {
					e.preventDefault();
					const langCode = this.getAttribute('data-lang');
					const isOriginal = this.getAttribute('data-original') === 'true';

					// Remove active class from all buttons
					langButtons.forEach(btn => btn.classList.remove('active'));

					// Add active class to clicked button
					this.classList.add('active');

					// Update statistics visibility
					statsContainers.forEach(container => {
						container.style.display = 'none';
						container.classList.remove('active');
					});
					const activeStatsContainer = block.querySelector(`.stats-container[data-lang="${langCode}"]`);
					if (activeStatsContainer) {
						activeStatsContainer.style.display = 'flex';
						activeStatsContainer.classList.add('active');
					}

					if (isOriginal) {
						// Hide all translation lines
						translationLines.forEach(line => {
							line.style.display = 'none';
						});
					} else {
						// Hide all translation lines first
						translationLines.forEach(line => {
							line.style.display = 'none';
						});
						// Show selected translation lines
						const selectedLines = block.querySelectorAll(`.translation-line[data-lang="${langCode}"]`);
						selectedLines.forEach(line => {
							line.style.display = 'block';
						});
					}

					// Update URL
					updateURL(langCode, isOriginal);

					// Update FAQ section with new language
					updateFAQLanguage(langCode, isOriginal);

					// Store preference in localStorage
					try {
						localStorage.setItem('arcuras_preferred_lyrics_lang', langCode);
					} catch (e) {
						// Ignore localStorage errors
					}
				});
			});

			// Check URL for language code first
			const urlLang = getLangFromURL();
			if (urlLang) {
				const urlButton = block.querySelector(`.lang-button[data-lang="${urlLang}"]`);
				if (urlButton && urlButton.getAttribute('data-original') !== 'true') {
					urlButton.click();
					return; // Don't check localStorage if URL has language
				}
			}

			// DON'T auto-load preferred language from localStorage
			// This was causing automatic redirects when clicking from archive pages
			// Users should explicitly click the translation they want to see
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			initLyricsBlocks();
			// Initialize FAQ accordion on page load
			if (typeof window.initializeFAQ === 'function') {
				window.initializeFAQ();
			}
		});
	} else {
		initLyricsBlocks();
		// Initialize FAQ accordion on page load
		if (typeof window.initializeFAQ === 'function') {
			window.initializeFAQ();
		}
	}

	// Re-initialize on dynamic content load (for AJAX)
	document.addEventListener('arcuras:lyrics-block-loaded', initLyricsBlocks);
})();
