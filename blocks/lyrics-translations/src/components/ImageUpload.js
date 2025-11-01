/**
 * Image Upload Component with Gemini Vision OCR
 * Allows users to upload an image and extract text using Google Gemini Vision API
 */
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const ImageUpload = ({ onTextExtracted, languageCode = 'eng', currentLyrics = '' }) => {
	const [isProcessing, setIsProcessing] = useState(false);
	const [progress, setProgress] = useState(0);
	const [previewUrl, setPreviewUrl] = useState(null);
	const [error, setError] = useState(null);
	const [extractedTexts, setExtractedTexts] = useState([]);
	const [imageCount, setImageCount] = useState(0);
	const [initialLyrics] = useState(currentLyrics); // Store initial lyrics once

	// Process image with Gemini Vision API
	const processImage = async (file) => {
		// Validate file type
		if (!file.type.startsWith('image/')) {
			setError(__('Please upload a valid image file.', 'gufte'));
			return;
		}

		// Create preview
		const reader = new FileReader();
		reader.onload = (e) => {
			setPreviewUrl(e.target.result);
		};
		reader.readAsDataURL(file);

		// Reset states
		setError(null);
		setIsProcessing(true);
		setProgress(50); // Show progress

		try {
			// Convert image to base64
			const base64Promise = new Promise((resolve, reject) => {
				const reader = new FileReader();
				reader.onload = () => resolve(reader.result);
				reader.onerror = reject;
				reader.readAsDataURL(file);
			});

			const base64Data = await base64Promise;

			// Call Gemini OCR API via WordPress AJAX
			const formData = new FormData();
			formData.append('action', 'arcuras_gemini_ocr');
			formData.append('nonce', window.arcurasGeminiOCR?.nonce || '');
			formData.append('image', base64Data);

			setProgress(70);

			const response = await fetch(window.arcurasGeminiOCR?.ajax_url || '/wp-admin/admin-ajax.php', {
				method: 'POST',
				body: formData,
			});

			setProgress(90);

			const result = await response.json();

			if (!result.success) {
				throw new Error(result.data || 'OCR failed');
			}

			const extractedText = result.data.text;

			// Store extracted text and combine with previous extractions
			if (extractedText) {
				const newTexts = [...extractedTexts, extractedText];
				setExtractedTexts(newTexts);
				setImageCount(prev => prev + 1);

				// Combine: initial lyrics + all newly extracted texts
				const allTexts = initialLyrics ? [initialLyrics, ...newTexts] : newTexts;
				const combinedText = allTexts.join('\n\n');
				onTextExtracted(combinedText);
				setProgress(100);
			} else {
				setError(__('No text found in the image. Please try a different image.', 'gufte'));
			}
		} catch (err) {
			console.error('Gemini OCR Error:', err);
			setError(__('Failed to extract text. Error: ', 'gufte') + err.message);
		} finally {
			setIsProcessing(false);
			setTimeout(() => setProgress(0), 500);
		}
	};

	const handleImageUpload = async (event) => {
		const file = event.target.files?.[0];
		if (!file) return;
		await processImage(file);
	};

	// Handle paste from clipboard
	const handlePaste = async (event) => {
		const items = event.clipboardData?.items;
		if (!items) return;

		// Find image in clipboard
		for (let i = 0; i < items.length; i++) {
			if (items[i].type.startsWith('image/')) {
				event.preventDefault();
				const file = items[i].getAsFile();
				if (file) {
					await processImage(file);
				}
				break;
			}
		}
	};

	const clearPreview = () => {
		setPreviewUrl(null);
		setError(null);
	};

	const clearAll = () => {
		setPreviewUrl(null);
		setError(null);
		setExtractedTexts([]);
		setImageCount(0);
		// Reset to initial lyrics (before any OCR was done)
		onTextExtracted(initialLyrics);
	};

	return (
		<div style={{
			marginTop: '16px',
			padding: '16px',
			border: '2px dashed #667eea',
			borderRadius: '8px',
			backgroundColor: '#f0f7ff'
		}}>
			<div style={{ marginBottom: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
				<strong style={{ color: '#667eea', fontSize: '14px' }}>
					📷 {__('Extract Lyrics from Image', 'gufte')}
					{imageCount > 0 && (
						<span style={{ marginLeft: '8px', fontSize: '12px', color: '#10b981', fontWeight: 'normal' }}>
							✓ {imageCount} {__('image(s) processed', 'gufte')}
						</span>
					)}
				</strong>
				{imageCount > 0 && (
					<Button
						isSmall
						isDestructive
						onClick={clearAll}
					>
						{__('Clear All', 'gufte')}
					</Button>
				)}
			</div>

			<p style={{ fontSize: '13px', color: '#666', marginBottom: '8px' }}>
				{__('Upload an image or paste from clipboard (Cmd+V / Ctrl+V) containing lyrics text.', 'gufte')}
				{' '}
				<strong>{__('You can upload multiple images', 'gufte')}</strong> {__('- they will be combined automatically.', 'gufte')}
			</p>

			<p style={{ fontSize: '12px', color: '#10b981', marginBottom: '12px', backgroundColor: '#ecfdf5', padding: '8px', borderRadius: '4px', border: '1px solid #6ee7b7' }}>
				✨ {__('Powered by Google Gemini Vision - Advanced AI text extraction with high accuracy', 'gufte')}
			</p>

			{/* Paste Area */}
			<div
				contentEditable
				onPaste={handlePaste}
				style={{
					padding: '20px',
					marginBottom: '12px',
					border: '2px dashed #667eea',
					borderRadius: '8px',
					backgroundColor: 'white',
					textAlign: 'center',
					cursor: 'text',
					minHeight: '80px',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					fontSize: '13px',
					color: '#999'
				}}
				suppressContentEditableWarning
			>
				{isProcessing ? (
					<span style={{ color: '#667eea' }}>⏳ {__('Processing...', 'gufte')}</span>
				) : (
					<span>
						📋 {__('Click here and paste image (Cmd+V / Ctrl+V)', 'gufte')}
						<br />
						<span style={{ fontSize: '11px' }}>
							{__('or use the upload button below', 'gufte')}
						</span>
					</span>
				)}
			</div>

			{/* Preview Image */}
			{previewUrl && (
				<div style={{ marginBottom: '12px' }}>
					<img
						src={previewUrl}
						alt="Preview"
						style={{
							maxWidth: '100%',
							maxHeight: '200px',
							objectFit: 'contain',
							border: '1px solid #ddd',
							borderRadius: '4px'
						}}
					/>
					<Button
						isSmall
						isDestructive
						onClick={clearPreview}
						style={{ marginTop: '8px' }}
					>
						{__('Clear', 'gufte')}
					</Button>
				</div>
			)}

			{/* Upload Button */}
			<div style={{ display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' }}>
				<input
					type="file"
					accept="image/*"
					onChange={handleImageUpload}
					disabled={isProcessing}
					id="lyrics-image-upload"
					style={{ display: 'none' }}
				/>
				<label htmlFor="lyrics-image-upload" style={{ cursor: 'pointer', display: 'inline-block' }}>
					<Button
						variant="primary"
						disabled={isProcessing}
						onClick={(e) => {
							// Prevent button default behavior, let label trigger file input
							e.preventDefault();
							document.getElementById('lyrics-image-upload')?.click();
						}}
					>
						{isProcessing
							? `${__('Processing...', 'gufte')} ${progress}%`
							: '📷 ' + __('Upload Image', 'gufte')
						}
					</Button>
				</label>

				{isProcessing && (
					<span style={{ fontSize: '13px', color: '#667eea' }}>
						⏳ {__('Extracting text...', 'gufte')}
					</span>
				)}
			</div>

			{/* Progress Bar */}
			{isProcessing && progress > 0 && (
				<div style={{
					marginTop: '12px',
					height: '8px',
					backgroundColor: '#e0e0e0',
					borderRadius: '4px',
					overflow: 'hidden'
				}}>
					<div style={{
						height: '100%',
						width: `${progress}%`,
						backgroundColor: '#667eea',
						transition: 'width 0.3s ease'
					}} />
				</div>
			)}

			{/* Error Message */}
			{error && (
				<div style={{
					marginTop: '12px',
					padding: '10px',
					backgroundColor: '#fee',
					border: '1px solid #fcc',
					borderRadius: '4px',
					color: '#c00',
					fontSize: '13px'
				}}>
					❌ {error}
				</div>
			)}

			{/* Supported Languages Info */}
			<div style={{
				marginTop: '12px',
				fontSize: '12px',
				color: '#999'
			}}>
				💡 {__('Tip: Works best with clear, high-contrast images. Current language:', 'gufte')} <strong>{languageCode.toUpperCase()}</strong>
			</div>
		</div>
	);
};

export default ImageUpload;
