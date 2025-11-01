/**
 * AI Translation Utility
 * Handles DeepSeek API integration for lyrics translation
 */

/**
 * Translate lyrics using AI
 * @param {string} text - Text to translate
 * @param {string} targetLang - Target language code
 * @param {string} sourceLang - Source language code
 * @returns {Promise<string>} Translated text
 */
export async function translateWithAI(text, targetLang, sourceLang = 'en') {
    if (!window.arcurasAI || !window.arcurasAI.has_api_key) {
        throw new Error('DeepSeek API key not configured. Please add it in Arcuras Theme Settings.');
    }

    const formData = new FormData();
    formData.append('action', 'arcuras_ai_translate');
    formData.append('nonce', window.arcurasAI.nonce);
    formData.append('text', text);
    formData.append('target_lang', targetLang);
    formData.append('source_lang', sourceLang);

    const response = await fetch(window.arcurasAI.ajax_url, {
        method: 'POST',
        body: formData,
    });

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.data || 'Translation failed');
    }

    return data.data.translated_text;
}
