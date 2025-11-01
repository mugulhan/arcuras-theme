/**
 * Lyrics & Translations Block - Main Entry Point
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './components/Edit';

/**
 * Register the block
 */
registerBlockType('arcuras/lyrics-translations', {
	title: __('Lyrics & Translations', 'gufte'),
	icon: 'translation',
	category: 'widgets',
	supports: {
		align: false,
		anchor: false,
		customClassName: true,
		html: false,
		reusable: true
	},
	attributes: {
		languages: {
			type: 'array',
			default: [
				{
					code: 'en',
					name: 'English',
					lyrics: '',
					isOriginal: true
				}
			]
		},
		autoDetectSections: {
			type: 'boolean',
			default: true
		},
		sectionComments: {
			type: 'object',
			default: {}
		}
	},
	edit: Edit,
	save: () => {
		// Return null to use PHP render callback (dynamic block)
		return null;
	}
});
