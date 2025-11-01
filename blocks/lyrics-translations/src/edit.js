/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	TabPanel,
	SelectControl,
	ToggleControl,
	BaseControl
} from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Custom Numbered Textarea Component
 */
const NumberedTextarea = ({ label, value, onChange, help, rows = 20 }) => {
	const [lineCount, setLineCount] = useState(0);
	const textareaRef = useRef(null);
	const lineNumbersRef = useRef(null);

	// Calculate non-empty line count
	const getNonEmptyLineCount = (text) => {
		if (!text) return 0;
		const lines = text.split('\n');
		return lines.filter(line => line.trim() !== '').length;
	};

	// Update line count when value changes
	useEffect(() => {
		setLineCount(getNonEmptyLineCount(value || ''));
	}, [value]);

	// Sync scroll between textarea and line numbers
	const handleScroll = (e) => {
		if (lineNumbersRef.current) {
			lineNumbersRef.current.scrollTop = e.target.scrollTop;
		}
	};

	// Generate line numbers HTML (skip empty lines)
	const generateLineNumbers = () => {
		const text = value || '';
		const lines = text.split('\n');
		let lineNumber = 1;

		return lines.map((line, index) => {
			const isEmpty = line.trim() === '';
			const number = isEmpty ? '' : lineNumber;

			if (!isEmpty) lineNumber++;

			return (
				<div
					key={index}
					className={`line-number${isEmpty ? ' empty' : ''}`}
					style={{ minWidth: '20px', textAlign: 'right' }}
				>
					{number}
				</div>
			);
		});
	};

	const handlePaste = (e) => {
		e.preventDefault();

		// Get plain text from clipboard
		const text = (e.clipboardData || window.clipboardData).getData('text/plain') || '';

		// Get current selection
		const textarea = e.target;
		const start = textarea.selectionStart;
		const end = textarea.selectionEnd;
		const currentValue = textarea.value || '';

		// Insert pasted text
		const newValue = currentValue.substring(0, start) + text + currentValue.substring(end);

		// Update via onChange
		onChange(newValue);

		// Set cursor position
		setTimeout(() => {
			textarea.selectionStart = textarea.selectionEnd = start + text.length;
			textarea.focus();
		}, 10);
	};

	const handleChange = (e) => {
		onChange(e.target.value);
	};

	return (
		<BaseControl
			label={label}
			help={help ? (
				<>
					{help}
					<div style={{ marginTop: '8px', fontSize: '12px', color: '#666', fontWeight: '500' }}>
						{__('Non-empty lines: ', 'gufte') + lineCount}
					</div>
				</>
			) : (
				<div style={{ fontSize: '12px', color: '#666', fontWeight: '500', marginTop: '4px' }}>
					{__('Non-empty lines: ', 'gufte') + lineCount}
				</div>
			)}
			className="numbered-textarea-control"
		>
			<div
				className="numbered-textarea-wrapper"
				style={{
					position: 'relative',
					border: '1px solid #949494',
					borderRadius: '2px',
					backgroundColor: '#fff',
					paddingLeft: '48px'
				}}
			>
				{/* Line numbers column */}
				<div
					ref={lineNumbersRef}
					className="line-numbers"
					style={{
						position: 'absolute',
						left: 0,
						top: 0,
						bottom: 0,
						width: '48px',
						padding: '8px 4px',
						backgroundColor: '#f7f7f7',
						borderRight: '1px solid #ddd',
						fontSize: '13px',
						lineHeight: '1.4',
						color: '#666',
						userSelect: 'none',
						overflow: 'hidden',
						fontFamily: 'monospace',
						pointerEvents: 'none'
					}}
				>
					{generateLineNumbers()}
				</div>

				{/* Textarea */}
				<textarea
					ref={textareaRef}
					value={value || ''}
					onPaste={handlePaste}
					onChange={handleChange}
					onScroll={handleScroll}
					rows={rows}
					style={{
						width: '100%',
						padding: '8px 12px',
						border: 'none',
						outline: 'none',
						resize: 'vertical',
						fontSize: '13px',
						lineHeight: '1.4',
						fontFamily: 'monospace',
						minHeight: `${rows * 22}px`,
						backgroundColor: 'transparent',
						whiteSpace: 'pre-wrap'
					}}
				/>
			</div>
		</BaseControl>
	);
};

export default NumberedTextarea;
