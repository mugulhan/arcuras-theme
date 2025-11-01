/**
 * Edit Component - Main Editor Interface
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Button, TabPanel, SelectControl, ComboboxControl, ToggleControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import NumberedTextarea from './NumberedTextarea';
import ImageUpload from './ImageUpload';
import { AVAILABLE_LANGUAGES } from '../constants';
import { getSEOPreview } from '../constants';
import {
	autoDetectSectionsFromLyrics,
	getAllSections,
	getNonEmptyLineCount,
	syncStructureFromOriginal
} from '../utils';
import { translateWithAI } from '../utils/ai-translation';

const Edit = ({ attributes, setAttributes }) => {
	const { languages, sectionComments, autoDetectSections } = attributes;

	// AI Translation state
	const [translatingLang, setTranslatingLang] = useState(null);

	// SEO settings from database
	const [seoSettings, setSeoSettings] = useState(null);

	// Available languages from database
	const [availableLanguages, setAvailableLanguages] = useState(AVAILABLE_LANGUAGES);

	// Get post title for SEO preview
	const postTitle = useSelect(
		(select) => select('core/editor')?.getEditedPostAttribute('title') || 'Song Title',
		[]
	);

	// Fetch SEO settings and available languages from database on mount
	useEffect(() => {
		apiFetch({ path: '/arcuras/v1/language-seo-settings' })
			.then((settings) => {
				setSeoSettings(settings);

				// Convert SEO settings to available languages format
				const languagesFromDB = Object.keys(settings).map(code => ({
					value: code,
					label: settings[code].name ? `${settings[code].name} (${code.toUpperCase()})` : code.toUpperCase()
				}));

				// Merge with existing languages and remove duplicates
				const mergedLanguages = [...AVAILABLE_LANGUAGES];
				languagesFromDB.forEach(dbLang => {
					if (!mergedLanguages.some(lang => lang.value === dbLang.value)) {
						mergedLanguages.push(dbLang);
					}
				});

				// Sort alphabetically by label
				mergedLanguages.sort((a, b) => a.label.localeCompare(b.label));

				setAvailableLanguages(mergedLanguages);
			})
			.catch((error) => {
				console.error('Error fetching SEO settings:', error);
			});
	}, []);

	// Get block props
	const blockProps = useBlockProps({
		className: 'lyrics-block-wrapper'
	});

	// Language management functions
	const addLanguage = (langCode, langName) => {
		const exists = languages.some(lang => lang.code === langCode);

		if (exists) {
			alert(__('This language is already added!', 'gufte'));
			return;
		}

		setAttributes({
			languages: [
				...languages,
				{
					code: langCode,
					name: langName,
					lyrics: '',
					isOriginal: false
				}
			]
		});
	};

	const removeLanguage = (index) => {
		if (languages.length <= 1) {
			alert(__('You must have at least one language!', 'gufte'));
			return;
		}
		setAttributes({
			languages: languages.filter((_, i) => i !== index)
		});
	};

	const updateLanguage = (index, field, value) => {
		const newLanguages = languages.map((lang, i) => {
			if (i === index) {
				return { ...lang, [field]: value };
			}
			return lang;
		});
		setAttributes({ languages: newLanguages });
	};

	// AI Translation handler
	const handleAITranslate = async (targetIndex) => {
		const targetLang = languages[targetIndex];
		const originalLang = languages.find(l => l.isOriginal);

		if (!originalLang || !originalLang.lyrics) {
			alert(__('Please add original lyrics first before translating.', 'gufte'));
			return;
		}

		if (!window.arcurasAI || !window.arcurasAI.has_api_key) {
			alert(__('AI API key not configured. Please add Groq or DeepSeek API key in Theme Settings → AI Translation.', 'gufte'));
			return;
		}

		setTranslatingLang(targetIndex);

		try {
			const translatedText = await translateWithAI(
				originalLang.lyrics,
				targetLang.code,
				originalLang.code
			);

			updateLanguage(targetIndex, 'lyrics', translatedText);
		} catch (error) {
			alert(__('Translation failed: ', 'gufte') + error.message);
		} finally {
			setTranslatingLang(null);
		}
	};

	const toggleOriginal = (index) => {
		const newLanguages = languages.map((lang, i) => {
			if (i === index) {
				return {
					...lang,
					isOriginal: !lang.isOriginal
				};
			}
			return lang;
		});
		setAttributes({ languages: newLanguages });
	};

	const handleSyncStructure = (index) => {
		if (confirm(__('This will reorganize your translation to match the original line structure. Continue?', 'gufte'))) {
			const syncedLyrics = syncStructureFromOriginal(languages, index);
			if (syncedLyrics !== null) {
				updateLanguage(index, 'lyrics', syncedLyrics);
			}
		}
	};

	// Section comments functions
	const updateSectionComment = (sectionName, langCode, comment) => {
		const key = `${sectionName}__${langCode}`;
		const newComments = { ...sectionComments };

		if (comment && comment.trim()) {
			newComments[key] = comment.trim();
		} else {
			delete newComments[key];
		}

		setAttributes({ sectionComments: newComments });
	};

	const getSectionComment = (sectionName, langCode) => {
		const key = `${sectionName}__${langCode}`;
		return sectionComments?.[key] || '';
	};

	// Get available languages that haven't been added yet
	const getAvailableLanguages = () => {
		return availableLanguages.filter(
			lang => !languages.some(addedLang => addedLang.code === lang.value)
		);
	};

	// Create tabs for TabPanel
	const tabs = languages.map((lang, index) => ({
		name: lang.code,
		title: lang.name + (lang.isOriginal ? ' ★' : ''),
		className: 'lyrics-tab-' + lang.code
	}));

	// Line count mismatch warning
	const renderLineCountWarning = () => {
		const lineCounts = languages.map(lang => ({
			name: lang.name,
			count: getNonEmptyLineCount(lang.lyrics),
			isOriginal: lang.isOriginal
		}));

		const originalCount = lineCounts.find(lc => lc.isOriginal);

		if (!originalCount || originalCount.count === 0) return null;

		const mismatchedLangs = lineCounts.filter(
			lc => !lc.isOriginal && lc.count > 0 && lc.count !== originalCount.count
		);

		if (mismatchedLangs.length === 0) return null;

		return (
			<div style={{
				padding: '12px',
				margin: '12px 0',
				backgroundColor: '#fff3cd',
				border: '1px solid #ffc107',
				borderRadius: '4px',
				fontSize: '13px'
			}}>
				<strong style={{ color: '#856404', display: 'block', marginBottom: '8px' }}>
					⚠️ {__('Line Count Mismatch', 'gufte')}
				</strong>
				<div style={{ color: '#856404' }}>
					{__('Original', 'gufte')} ({originalCount.name}): {originalCount.count} {__('lines', 'gufte')}
				</div>
				{mismatchedLangs.map((lang, i) => (
					<div key={i} style={{ color: '#856404' }}>
						{lang.name}: {lang.count} {__('lines', 'gufte')} (
						{lang.count > originalCount.count ? '+' : ''}
						{lang.count - originalCount.count})
					</div>
				))}
			</div>
		);
	};

	// Render detected sections info
	const renderDetectedSections = () => {
		if (!autoDetectSections) return null;

		const originalLang = languages.find(lang => lang.isOriginal) || languages[0];

		if (!originalLang || !originalLang.lyrics) {
			return (
				<p className="components-base-control__help" style={{
					marginTop: '12px',
					padding: '12px',
					background: '#f9f9f9',
					borderRadius: '4px',
					fontSize: '13px',
					color: '#666'
				}}>
					{__('No sections detected yet. Add lyrics to see detected sections.', 'gufte')}
				</p>
			);
		}

		const sections = autoDetectSectionsFromLyrics(originalLang.lyrics);

		if (sections.length === 0) {
			return (
				<p className="components-base-control__help" style={{
					marginTop: '12px',
					padding: '12px',
					background: '#f9f9f9',
					borderRadius: '4px',
					fontSize: '13px',
					color: '#666'
				}}>
					{__('No sections detected yet. Add lyrics to see detected sections.', 'gufte')}
				</p>
			);
		}

		return (
			<div style={{
				marginTop: '16px',
				padding: '12px',
				background: '#f0f7ff',
				borderRadius: '4px',
				fontSize: '13px'
			}}>
				<strong style={{ display: 'block', marginBottom: '8px', color: '#667eea' }}>
					{__('Detected Sections:', 'gufte')}
				</strong>
				<ul style={{ margin: '0', paddingLeft: '20px', listStyle: 'disc' }}>
					{sections.map((section) => (
						<li key={`${section.name}_${section.startIndex}`} style={{
							marginBottom: '4px',
							color: '#1e1e1e'
						}}>
							<strong>{section.name}</strong>
							<span style={{ color: '#666', fontSize: '12px', marginLeft: '6px' }}>
								(Line {section.startIndex + 1})
							</span>
						</li>
					))}
				</ul>
			</div>
		);
	};

	// Render section comments panel
	const renderSectionComments = () => {
		const allSections = getAllSections(languages, autoDetectSections);

		if (allSections.length === 0) {
			return (
				<div style={{
					padding: '12px',
					background: '#f0f0f1',
					borderRadius: '4px',
					fontSize: '13px',
					textAlign: 'center',
					color: '#666'
				}}>
					{__('No sections detected. Add section markers like [Verse 1], [Chorus] to your lyrics.', 'gufte')}
				</div>
			);
		}

		return allSections.map(sectionName => (
			<div key={sectionName} style={{
				marginBottom: '20px',
				padding: '12px',
				border: '1px solid #ddd',
				borderRadius: '8px',
				background: '#fafafa'
			}}>
				<h4 style={{
					margin: '0 0 12px 0',
					fontSize: '13px',
					fontWeight: '600',
					color: '#1e1e1e'
				}}>
					📌 {sectionName}
				</h4>
				{languages.map(lang => {
					const commentValue = getSectionComment(sectionName, lang.code);
					const hasComment = commentValue.length > 0;

					return (
						<div key={lang.code} style={{ marginBottom: '12px' }}>
							<label style={{
								display: 'block',
								fontSize: '12px',
								fontWeight: '500',
								marginBottom: '4px',
								color: '#555'
							}}>
								{lang.name} {hasComment ? '✓' : ''}
							</label>
							<textarea
								value={commentValue}
								onChange={(e) => updateSectionComment(sectionName, lang.code, e.target.value)}
								placeholder={__('Add comment for this section in ' + lang.name + '...', 'gufte')}
								rows={2}
								style={{
									width: '100%',
									padding: '8px',
									fontSize: '13px',
									border: '1px solid #ddd',
									borderRadius: '4px',
									resize: 'vertical',
									fontFamily: 'inherit'
								}}
							/>
						</div>
					);
				})}
			</div>
		));
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Languages', 'gufte')} initialOpen={true}>
					<p className="components-base-control__help">
						{__('Add or remove translation languages for this song.', 'gufte')}
					</p>
				</PanelBody>

				<PanelBody title={__('Section Detection', 'gufte')} initialOpen={false}>
					<ToggleControl
						label={__('Auto-detect Sections', 'gufte')}
						help={autoDetectSections
							? __('Automatically detects Verse and Chorus sections based on repetition patterns.', 'gufte')
							: __('Use manual [Verse 1], [Chorus] markers in your lyrics.', 'gufte')
						}
						checked={autoDetectSections || false}
						onChange={(value) => setAttributes({ autoDetectSections: value })}
					/>
					{!autoDetectSections && (
						<p className="components-base-control__help" style={{
							marginTop: '12px',
							padding: '12px',
							background: '#f0f7ff',
							borderRadius: '4px',
							fontSize: '13px'
						}}>
							{__('Manual mode: Add [Verse 1], [Chorus], [Bridge], etc. at the start of lines.', 'gufte')}
						</p>
					)}
					{renderDetectedSections()}
				</PanelBody>

				<PanelBody title={__('Section Comments', 'gufte')} initialOpen={false}>
					<p className="components-base-control__help" style={{ marginBottom: '16px' }}>
						{__('Add explanatory comments for each section. Comments will appear when users click on the section.', 'gufte')}
					</p>
					{renderSectionComments()}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="lyrics-translations-editor">
					{/* Block Header */}
					<div className="block-header">
						<svg
							className="block-icon"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
						>
							<path
								d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"
								fill="currentColor"
							/>
						</svg>
						<h3>{__('Lyrics & Translations', 'gufte')}</h3>
					</div>

					{/* Language Selector */}
					{getAvailableLanguages().length > 0 && (
						<div className="language-selector-wrapper">
							<ComboboxControl
								label={__('Add Translation:', 'gufte')}
								value={null}
								options={getAvailableLanguages().map(lang => ({
									value: lang.value,
									label: lang.label
								}))}
								onChange={(value) => {
									if (value) {
										const selectedLang = availableLanguages.find(l => l.value === value);
										if (selectedLang) {
											addLanguage(selectedLang.value, selectedLang.label);
										}
									}
								}}
								placeholder={__('Search for a language...', 'gufte')}
								help={__('Can\'t find your language? Add it from SEO Settings > Manage Languages in the dashboard.', 'gufte')}
							/>
						</div>
					)}

					{/* Line Count Warning */}
					{renderLineCountWarning()}

					{/* Tab Panel */}
					<TabPanel
						className="lyrics-tab-panel"
						activeClass="is-active"
						tabs={tabs}
						key={JSON.stringify(languages.map(l => ({ code: l.code, isOriginal: l.isOriginal })))}
					>
						{(tab) => {
							const index = languages.findIndex(l => l.code === tab.name);
							const lang = languages[index];

							// Generate both Original and Translation SEO previews
							const originalSeoPreview = getSEOPreview({ ...lang, isOriginal: true }, postTitle, seoSettings);
							const translationSeoPreview = getSEOPreview({ ...lang, isOriginal: false }, postTitle, seoSettings);

							return (
								<div className="lyrics-tab-content">
									{/* Language Settings */}
									<div className="language-settings">
										<ComboboxControl
											label={__('Language', 'gufte')}
											value={lang.code}
											options={availableLanguages.map(l => ({
												value: l.value,
												label: l.label
											}))}
											onChange={(value) => {
												const selectedLang = availableLanguages.find(l => l.value === value);
												const newLanguages = languages.map((l, i) => {
													if (i === index) {
														return {
															...l,
															code: value,
															name: selectedLang ? selectedLang.label : value
														};
													}
													return l;
												});
												setAttributes({ languages: newLanguages });
											}}
										/>
										<ToggleControl
											label={__('Original Language', 'gufte')}
											help={__('Mark this as an original language. You can select multiple originals for bilingual songs.', 'gufte')}
											checked={lang.isOriginal || false}
											onChange={() => {
												toggleOriginal(index);
											}}
										/>
										<div style={{ marginTop: '10px', display: 'flex', gap: '8px' }}>
											{!lang.isOriginal && (
												<Button
													variant="secondary"
													onClick={() => handleSyncStructure(index)}
												>
													📋 {__('Copy Structure from Original', 'gufte')}
												</Button>
											)}
											{languages.length > 1 && (
												<Button
													variant="tertiary"
													isDestructive
													onClick={() => removeLanguage(index)}
												>
													{__('Remove This Language', 'gufte')}
												</Button>
											)}
										</div>
									</div>

									{/* AI Translation */}
									{!lang.isOriginal && (
										<div style={{
											marginTop: '16px',
											padding: '16px',
											border: '2px dashed #10b981',
											borderRadius: '8px',
											backgroundColor: '#f0fdf4'
										}}>
											<div style={{ marginBottom: '12px' }}>
												<strong style={{ color: '#10b981', fontSize: '14px' }}>
													🤖 {__('AI Translation', 'gufte')}
												</strong>
											</div>
											<p style={{ fontSize: '13px', color: '#666', marginBottom: '12px' }}>
												{__('Automatically translate from original lyrics using Groq AI (free & fast).', 'gufte')}
											</p>
											<Button
												variant="primary"
												onClick={() => handleAITranslate(index)}
												disabled={translatingLang !== null}
												style={{ backgroundColor: '#10b981', borderColor: '#10b981' }}
											>
												{translatingLang === index ? (
													<>
														<Spinner />
														{__('Translating...', 'gufte')}
													</>
												) : (
													<>🤖 {__('Translate with AI', 'gufte')}</>
												)}
											</Button>
											{window.arcurasAI?.model_display && (
												<p style={{ marginTop: '8px', fontSize: '11px', color: '#059669', fontWeight: '500' }}>
													Using: {window.arcurasAI.provider} - {window.arcurasAI.model_display}
												</p>
											)}
										</div>
									)}

									{/* Image Upload OCR */}
									<ImageUpload
										languageCode={lang.code}
										currentLyrics={lang.lyrics || ''}
										onTextExtracted={(extractedText) => {
											// ImageUpload handles combining multiple images,
											// so just set the text directly
											updateLanguage(index, 'lyrics', extractedText);
										}}
									/>

									{/* Lyrics Textarea */}
									<NumberedTextarea
										label={__('Lyrics', 'gufte')}
										help={__('Enter lyrics line by line. Use special markers: [Verse 1], [Chorus], [Bridge], etc. at the start of a line to create section headers.', 'gufte')}
										value={lang.lyrics}
										onChange={(value) => {
											// Convert tabs to newlines (for table paste)
											const processedValue = value.replace(/\t/g, '\n');
											updateLanguage(index, 'lyrics', processedValue);
										}}
										rows={20}
									/>

									{/* Section Markers Help */}
									<div className="section-markers-help">
										<strong>{__('Section Markers:', 'gufte')}</strong>
										<br />
										<code>[Verse 1]</code> <code>[Verse 2]</code> <code>[Chorus]</code> <code>[Bridge]</code> <code>[Outro]</code>
										<br />
										<small>{__('Add these at the beginning of a line to create section headers', 'gufte')}</small>
									</div>

									{/* Stats */}
									<div className="lyrics-stats">
										<span className="stat-item">
											{__('Lines:', 'gufte')} {lang.lyrics ? lang.lyrics.split('\n').filter(line => line.trim()).length : 0}
										</span>
										<span className="stat-separator"> • </span>
										<span className="stat-item">
											{__('Words:', 'gufte')} {lang.lyrics ? lang.lyrics.split(/\s+/).filter(word => word.trim()).length : 0}
										</span>
									</div>

									{/* SEO Preview - Detailed */}
									<div style={{
										marginTop: '16px',
										padding: '16px',
										background: '#f0f7ff',
										border: '1px solid #667eea',
										borderRadius: '8px'
									}}>
										<div style={{
											fontSize: '12px',
											fontWeight: '600',
											color: '#667eea',
											marginBottom: '16px',
											textTransform: 'uppercase',
											letterSpacing: '0.5px'
										}}>
											🔍 SEO Preview ({lang.name || lang.code.toUpperCase()})
										</div>

										{/* Original Language SEO */}
										<div style={{
											marginBottom: '16px',
											paddingBottom: '16px',
											borderBottom: '1px solid #d0e0f5'
										}}>
											<div style={{
												fontSize: '11px',
												fontWeight: '600',
												color: '#4a5568',
												marginBottom: '8px',
												display: 'flex',
												alignItems: 'center',
												gap: '6px'
											}}>
												<span style={{
													background: '#3b82f6',
													color: 'white',
													padding: '2px 8px',
													borderRadius: '4px',
													fontSize: '10px'
												}}>
													ORIGINAL
												</span>
												When this language is the original
											</div>
											<div style={{ marginBottom: '8px' }}>
												<div style={{
													fontSize: '10px',
													color: '#888',
													marginBottom: '3px'
												}}>
													Title:
												</div>
												<div style={{
													fontSize: '16px',
													color: '#1a0dab',
													fontWeight: '400',
													lineHeight: '1.3'
												}}>
													{originalSeoPreview.title}
												</div>
											</div>
											<div>
												<div style={{
													fontSize: '10px',
													color: '#888',
													marginBottom: '3px'
												}}>
													Meta Description:
												</div>
												<div style={{
													fontSize: '12px',
													color: '#545454',
													lineHeight: '1.4'
												}}>
													{originalSeoPreview.description}
												</div>
											</div>
										</div>

										{/* Translation Language SEO */}
										<div>
											<div style={{
												fontSize: '11px',
												fontWeight: '600',
												color: '#4a5568',
												marginBottom: '8px',
												display: 'flex',
												alignItems: 'center',
												gap: '6px'
											}}>
												<span style={{
													background: '#10b981',
													color: 'white',
													padding: '2px 8px',
													borderRadius: '4px',
													fontSize: '10px'
												}}>
													TRANSLATION
												</span>
												When this language is a translation
											</div>
											<div style={{ marginBottom: '8px' }}>
												<div style={{
													fontSize: '10px',
													color: '#888',
													marginBottom: '3px'
												}}>
													Title:
												</div>
												<div style={{
													fontSize: '16px',
													color: '#1a0dab',
													fontWeight: '400',
													lineHeight: '1.3'
												}}>
													{translationSeoPreview.title}
												</div>
											</div>
											<div>
												<div style={{
													fontSize: '10px',
													color: '#888',
													marginBottom: '3px'
												}}>
													Meta Description:
												</div>
												<div style={{
													fontSize: '12px',
													color: '#545454',
													lineHeight: '1.4'
												}}>
													{translationSeoPreview.description}
												</div>
											</div>
										</div>
									</div>
								</div>
							);
						}}
					</TabPanel>
				</div>
			</div>
		</>
	);
};

export default Edit;
