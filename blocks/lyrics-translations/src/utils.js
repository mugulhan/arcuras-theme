/**
 * Utility functions for lyrics processing
 */
import { __ } from '@wordpress/i18n';

/**
 * Auto-detect sections based on repetition (like PHP version)
 */
export function autoDetectSectionsFromLyrics(lyrics) {
	if (!lyrics) return [];

	const lines = lyrics.split('\n');
	const blocks = [];
	let currentBlock = [];
	let blockStartIndex = -1;

	// Split into blocks separated by empty lines
	for (let i = 0; i < lines.length; i++) {
		const isEmpty = lines[i].trim() === '';

		if (isEmpty) {
			if (currentBlock.length > 0) {
				blocks.push({
					lines: currentBlock,
					startIndex: blockStartIndex,
					normalized: currentBlock.join(' ').toLowerCase().trim()
				});
				currentBlock = [];
				blockStartIndex = -1;
			}
		} else {
			if (currentBlock.length === 0) {
				blockStartIndex = i;
			}
			currentBlock.push(lines[i]);
		}
	}

	// Add last block
	if (currentBlock.length > 0) {
		blocks.push({
			lines: currentBlock,
			startIndex: blockStartIndex,
			normalized: currentBlock.join(' ').toLowerCase().trim()
		});
	}

	// Find repeating blocks (chorus candidates)
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

		// If block appears more than once, it's likely a chorus
		if (occurrences >= 2) {
			if (!labeledNormalized.has(normalized)) {
				sections.push({
					name: 'Chorus',
					type: 'chorus',
					startIndex: block.startIndex
				});
				labeledNormalized.add(normalized);
			}
		} else {
			// Unique block, likely a verse
			sections.push({
				name: 'Verse ' + verseCounter,
				type: 'verse',
				startIndex: block.startIndex
			});
			verseCounter++;
		}
	});

	return sections;
}

/**
 * Detect manual section markers like [Verse 1], [Chorus]
 */
export function detectManualSections(lyrics) {
	if (!lyrics) return [];
	const lines = lyrics.split('\n');
	const sections = [];
	const sectionPattern = /^\[(Verse \d+|Chorus|Bridge|Pre-Chorus|Intro|Outro|Hook|Interlude|Post-Chorus)\]/i;

	for (let i = 0; i < lines.length; i++) {
		const match = lines[i].match(sectionPattern);
		if (match) {
			sections.push({
				name: match[1],
				lineIndex: i
			});
		}
	}
	return sections;
}

/**
 * Get all unique sections across all languages
 */
export function getAllSections(languages, autoDetectSections) {
	const sectionsSet = new Set();

	// Find original language
	const originalLang = languages.find(lang => lang.isOriginal) || languages[0];

	if (!originalLang || !originalLang.lyrics) {
		return [];
	}

	// Check if auto-detect is enabled
	if (autoDetectSections) {
		// Use auto-detection on original language only
		const sections = autoDetectSectionsFromLyrics(originalLang.lyrics);
		sections.forEach(section => {
			sectionsSet.add(section.name);
		});
	} else {
		// Use manual markers across all languages
		languages.forEach(lang => {
			const sections = detectManualSections(lang.lyrics);
			sections.forEach(section => {
				sectionsSet.add(section.name);
			});
		});
	}

	return Array.from(sectionsSet).sort();
}

/**
 * Get non-empty line count
 */
export function getNonEmptyLineCount(text) {
	if (!text) return 0;
	const lines = text.split('\n');
	return lines.filter(line => line.trim() !== '').length;
}

/**
 * Sync structure from original to translation
 */
export function syncStructureFromOriginal(languages, translationIndex) {
	// Find original language
	const originalLang = languages.find(lang => lang.isOriginal);

	if (!originalLang || !originalLang.lyrics) {
		alert(__('No original lyrics found!', 'gufte'));
		return null;
	}

	const translationLang = languages[translationIndex];
	if (!translationLang || !translationLang.lyrics) {
		alert(__('Translation is empty!', 'gufte'));
		return null;
	}

	// Get original structure (empty line positions)
	const originalLines = originalLang.lyrics.split('\n');
	const translationLines = translationLang.lyrics.split('\n').filter(line => line.trim() !== '');

	// Build new translation with original structure
	const newTranslationLines = [];
	let translationLineIndex = 0;

	for (let i = 0; i < originalLines.length; i++) {
		const isOriginalEmpty = originalLines[i].trim() === '';

		if (isOriginalEmpty) {
			// Copy empty line from original
			newTranslationLines.push('');
		} else {
			// Add next translation line
			if (translationLineIndex < translationLines.length) {
				newTranslationLines.push(translationLines[translationLineIndex]);
				translationLineIndex++;
			} else {
				// Translation ran out of lines, add empty
				newTranslationLines.push('');
			}
		}
	}

	// If translation has more lines than original, warn user
	if (translationLineIndex < translationLines.length) {
		const remainingLines = translationLines.length - translationLineIndex;
		alert(
			__('Warning: Translation has ', 'gufte') +
			remainingLines +
			__(' more lines than original. Extra lines were not included.', 'gufte')
		);
	}

	return newTranslationLines.join('\n');
}
