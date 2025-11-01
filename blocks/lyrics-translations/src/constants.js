/**
 * Available languages with ISO codes
 */
export const AVAILABLE_LANGUAGES = [
	{ value: 'en', label: 'English' },
	{ value: 'es', label: 'Spanish (Español)' },
	{ value: 'tr', label: 'Turkish (Türkçe)' },
	{ value: 'fr', label: 'French (Français)' },
	{ value: 'de', label: 'German (Deutsch)' },
	{ value: 'it', label: 'Italian (Italiano)' },
	{ value: 'pt', label: 'Portuguese (Português)' },
	{ value: 'ar', label: 'Arabic (العربية)' },
	{ value: 'ru', label: 'Russian (Русский)' },
	{ value: 'ja', label: 'Japanese (日本語)' },
	{ value: 'ko', label: 'Korean (한국어)' },
	{ value: 'zh', label: 'Chinese (中文)' },
	{ value: 'hi', label: 'Hindi (हिन्दी)' },
	{ value: 'nl', label: 'Dutch (Nederlands)' },
	{ value: 'pl', label: 'Polish (Polski)' },
	{ value: 'sv', label: 'Swedish (Svenska)' },
	{ value: 'no', label: 'Norwegian (Norsk)' },
	{ value: 'da', label: 'Danish (Dansk)' },
	{ value: 'fi', label: 'Finnish (Suomi)' },
	{ value: 'el', label: 'Greek (Ελληνικά)' },
	{ value: 'he', label: 'Hebrew (עברית)' },
	{ value: 'uk', label: 'Ukrainian (Українська)' },
	{ value: 'cs', label: 'Czech (Čeština)' },
	{ value: 'ro', label: 'Romanian (Română)' },
	{ value: 'hu', label: 'Hungarian (Magyar)' },
	{ value: 'th', label: 'Thai (ไทย)' },
	{ value: 'vi', label: 'Vietnamese (Tiếng Việt)' },
	{ value: 'id', label: 'Indonesian (Bahasa Indonesia)' },
	{ value: 'ba', label: 'Bashkir (Башҡорт теле)' },
	{ value: 'az', label: 'Azerbaijani (Azərbaycan)' },
	{ value: 'kk', label: 'Kazakh (Қазақша)' }
];

/**
 * SEO Title/Description helpers
 */
export const SEO_DATA = {
	en: {
		original_suffix: 'Lyrics, Translations and Annotations',
		meta_suffix: 'Read lyrics, discover translations in multiple languages, and explore detailed annotations',
		translation_suffix: 'Translation',
		translation_meta_suffix: 'Translated lyrics with original text and annotations'
	},
	es: {
		original_suffix: 'Letras, Traducciones y Anotaciones',
		meta_suffix: 'Lee las letras, descubre traducciones en varios idiomas y explora anotaciones detalladas',
		translation_suffix: 'Traducción al Español',
		translation_meta_suffix: 'Letras traducidas al español con texto original y anotaciones'
	},
	tr: {
		original_suffix: 'Şarkı Sözleri, Çeviriler ve Açıklamalar',
		meta_suffix: 'Şarkı sözlerini okuyun, birden fazla dilde çevirileri keşfedin ve detaylı açıklamaları inceleyin',
		translation_suffix: 'Türkçe Çeviri',
		translation_meta_suffix: 'Orijinal metin ve açıklamalarla birlikte Türkçe çeviri'
	},
	de: {
		original_suffix: 'Liedtext, Übersetzungen und Anmerkungen',
		meta_suffix: 'Lesen Sie die Texte, entdecken Sie Übersetzungen in mehreren Sprachen und erkunden Sie detaillierte Anmerkungen',
		translation_suffix: 'Deutsche Übersetzung',
		translation_meta_suffix: 'Übersetzte Texte mit Originaltext und Anmerkungen'
	},
	fr: {
		original_suffix: 'Paroles, Traductions et Annotations',
		meta_suffix: 'Lisez les paroles, découvrez les traductions en plusieurs langues et explorez les annotations détaillées',
		translation_suffix: 'Traduction Française',
		translation_meta_suffix: 'Paroles traduites en français avec texte original et annotations'
	},
	ar: {
		original_suffix: 'كلمات الأغنية والترجمات والتعليقات',
		meta_suffix: 'اقرأ الكلمات، اكتشف الترجمات بعدة لغات، واستكشف التعليقات التفصيلية',
		translation_suffix: 'ترجمة عربية',
		translation_meta_suffix: 'كلمات مترجمة مع النص الأصلي والتعليقات'
	},
	it: {
		original_suffix: 'Testo, Traduzioni e Annotazioni',
		meta_suffix: 'Leggi i testi, scopri le traduzioni in più lingue ed esplora le annotazioni dettagliate',
		translation_suffix: 'Traduzione Italiana',
		translation_meta_suffix: 'Testo tradotto in italiano con testo originale e annotazioni'
	},
	pt: {
		original_suffix: 'Letras, Traduções e Anotações',
		meta_suffix: 'Leia as letras, descubra traduções em vários idiomas e explore anotações detalhadas',
		translation_suffix: 'Tradução em Português',
		translation_meta_suffix: 'Letras traduzidas em português com texto original e anotações'
	},
	ru: {
		original_suffix: 'Текст песни, переводы и комментарии',
		meta_suffix: 'Читайте тексты, открывайте переводы на несколько языков и изучайте подробные комментарии',
		translation_suffix: 'Русский перевод',
		translation_meta_suffix: 'Переведенный текст с оригиналом и комментариями'
	},
	ja: {
		original_suffix: '歌詞、翻訳、注釈',
		meta_suffix: '歌詞を読み、複数言語の翻訳を発見し、詳細な注釈を探索してください',
		translation_suffix: '日本語翻訳',
		translation_meta_suffix: '原文と注釈付きの日本語訳歌詞'
	},
	ko: {
		original_suffix: '가사, 번역 및 주석',
		meta_suffix: '가사를 읽고, 여러 언어로 된 번역을 발견하고, 자세한 주석을 살펴보세요',
		translation_suffix: '한국어 번역',
		translation_meta_suffix: '원문 및 주석과 함께 한국어로 번역된 가사'
	},
	zh: {
		original_suffix: '歌词、翻译和注释',
		meta_suffix: '阅读歌词，发现多语言翻译，探索详细注释',
		translation_suffix: '中文翻译',
		translation_meta_suffix: '带原文和注释的中文翻译歌词'
	},
	hi: {
		original_suffix: 'गीत, अनुवाद और टिप्पणियाँ',
		meta_suffix: 'गीत पढ़ें, कई भाषाओं में अनुवाद खोजें और विस्तृत टिप्पणियों का अन्वेषण करें',
		translation_suffix: 'हिंदी अनुवाद',
		translation_meta_suffix: 'मूल पाठ और टिप्पणियों के साथ हिंदी में अनुवादित गीत'
	},
	nl: {
		original_suffix: 'Songteksten, Vertalingen en Annotaties',
		meta_suffix: 'Lees songteksten, ontdek vertalingen in meerdere talen en verken gedetailleerde annotaties',
		translation_suffix: 'Nederlandse Vertaling',
		translation_meta_suffix: 'Nederlandse vertaling met originele tekst en annotaties'
	},
	pl: {
		original_suffix: 'Teksty Piosenek, Tłumaczenia i Adnotacje',
		meta_suffix: 'Czytaj teksty, odkrywaj tłumaczenia w wielu językach i przeglądaj szczegółowe adnotacje',
		translation_suffix: 'Polskie Tłumaczenie',
		translation_meta_suffix: 'Polskie tłumaczenie z oryginalnym tekstem i adnotacjami'
	},
	sv: {
		original_suffix: 'Texter, Översättningar och Kommentarer',
		meta_suffix: 'Läs texter, upptäck översättningar på flera språk och utforska detaljerade kommentarer',
		translation_suffix: 'Svensk Översättning',
		translation_meta_suffix: 'Svensk översättning med originaltext och kommentarer'
	},
	no: {
		original_suffix: 'Tekster, Oversettelser og Merknader',
		meta_suffix: 'Les tekster, oppdag oversettelser på flere språk og utforsk detaljerte merknader',
		translation_suffix: 'Norsk Oversettelse',
		translation_meta_suffix: 'Norsk oversettelse med originaltekst og merknader'
	},
	da: {
		original_suffix: 'Tekster, Oversættelser og Annotationer',
		meta_suffix: 'Læs tekster, opdag oversættelser på flere sprog og udforsk detaljerede annotationer',
		translation_suffix: 'Dansk Oversættelse',
		translation_meta_suffix: 'Dansk oversættelse med originaltekst og annotationer'
	},
	fi: {
		original_suffix: 'Sanoitukset, Käännökset ja Huomautukset',
		meta_suffix: 'Lue sanoituksia, löydä käännöksiä useilla kielillä ja tutustu yksityiskohtaisiin huomautuksiin',
		translation_suffix: 'Suomenkielinen Käännös',
		translation_meta_suffix: 'Suomenkielinen käännös alkuperäistekstin ja huomautusten kanssa'
	},
	el: {
		original_suffix: 'Στίχοι, Μεταφράσεις και Σημειώσεις',
		meta_suffix: 'Διαβάστε στίχους, ανακαλύψτε μεταφράσεις σε πολλές γλώσσες και εξερευνήστε λεπτομερείς σημειώσεις',
		translation_suffix: 'Ελληνική Μετάφραση',
		translation_meta_suffix: 'Ελληνική μετάφραση με αρχικό κείμενο και σημειώσεις'
	},
	he: {
		original_suffix: 'מילים, תרגומים והערות',
		meta_suffix: 'קרא מילים, גלה תרגומים במספר שפות וחקור הערות מפורטות',
		translation_suffix: 'תרגום עברי',
		translation_meta_suffix: 'תרגום עברי עם טקסט מקורי והערות'
	},
	uk: {
		original_suffix: 'Тексти пісень, переклади та коментарі',
		meta_suffix: 'Читайте тексти, відкривайте переклади кількома мовами та вивчайте детальні коментарі',
		translation_suffix: 'Український переклад',
		translation_meta_suffix: 'Український переклад з оригінальним текстом та коментарями'
	},
	cs: {
		original_suffix: 'Texty, Překlady a Poznámky',
		meta_suffix: 'Čtěte texty, objevujte překlady v několika jazycích a prozkoumávejte podrobné poznámky',
		translation_suffix: 'Český Překlad',
		translation_meta_suffix: 'Český překlad s původním textem a poznámkami'
	},
	ro: {
		original_suffix: 'Versuri, Traduceri și Adnotări',
		meta_suffix: 'Citește versuri, descoperă traduceri în mai multe limbi și explorează adnotări detaliate',
		translation_suffix: 'Traducere în Română',
		translation_meta_suffix: 'Traducere în română cu text original și adnotări'
	},
	hu: {
		original_suffix: 'Dalszövegek, Fordítások és Megjegyzések',
		meta_suffix: 'Olvasson dalszövegeket, fedezzen fel fordításokat több nyelven és fedezze fel a részletes megjegyzéseket',
		translation_suffix: 'Magyar Fordítás',
		translation_meta_suffix: 'Magyar fordítás az eredeti szöveggel és megjegyzésekkel'
	},
	th: {
		original_suffix: 'เนื้อเพลง, การแปล และคำอธิบาย',
		meta_suffix: 'อ่านเนื้อเพลง ค้นพบการแปลหลายภาษา และสำรวจคำอธิบายโดยละเอียด',
		translation_suffix: 'คำแปลภาษาไทย',
		translation_meta_suffix: 'เนื้อเพลงแปลเป็นภาษาไทยพร้อมข้อความต้นฉบับและคำอธิบาย'
	},
	vi: {
		original_suffix: 'Lời bài hát, Bản dịch và Chú thích',
		meta_suffix: 'Đọc lời bài hát, khám phá bản dịch bằng nhiều ngôn ngữ và khám phá chú thích chi tiết',
		translation_suffix: 'Bản dịch Tiếng Việt',
		translation_meta_suffix: 'Lời bài hát được dịch sang tiếng Việt với văn bản gốc và chú thích'
	},
	id: {
		original_suffix: 'Lirik, Terjemahan dan Anotasi',
		meta_suffix: 'Baca lirik, temukan terjemahan dalam berbagai bahasa dan jelajahi anotasi terperinci',
		translation_suffix: 'Terjemahan Bahasa Indonesia',
		translation_meta_suffix: 'Lirik terjemahan bahasa Indonesia dengan teks asli dan anotasi'
	},
	ba: {
		original_suffix: 'Йыр һүҙҙәре, тәржемәләр һәм аңлатмалар',
		meta_suffix: 'Йыр һүҙҙәрен уҡығыҙ, төрлө телдәрҙәге тәржемәләрҙе табығыҙ һәм тулы аңлатмаларҙы өйрәнегеҙ',
		translation_suffix: 'Башҡорт тәржемәһе',
		translation_meta_suffix: 'Башҡорт теленә тәржемә ителгән йыр һүҙҙәре, төп текст һәм аңлатмалар менән'
	},
	az: {
		original_suffix: 'Mahnı sözləri, Tərcümələr və Açıqlamalar',
		meta_suffix: 'Mahnı sözlərini oxuyun, müxtəlif dillərdə tərcümələri kəşf edin və ətraflı açıqlamaları araşdırın',
		translation_suffix: 'Azərbaycan dilinə tərcümə',
		translation_meta_suffix: 'Orijinal mətn və açıqlamalarla birlikdə Azərbaycan dilinə tərcümə edilmiş mahnı sözləri'
	},
	kk: {
		original_suffix: 'Ән сөздері, Аудармалар және Түсініктемелер',
		meta_suffix: 'Ән сөздерін оқыңыз, әртүрлі тілдердегі аудармаларды табыңыз және толық түсініктемелерді зерттеңіз',
		translation_suffix: 'Қазақ тіліне аударма',
		translation_meta_suffix: 'Түпнұсқа мәтін және түсініктемелермен бірге қазақ тіліне аударылған ән сөздері'
	}
};

/**
 * Get SEO preview for a language
 * @param {Object} lang - Language object with code, name, isOriginal
 * @param {string} postTitle - Post title
 * @param {Object} dbSettings - SEO settings from database (optional)
 */
export function getSEOPreview(lang, postTitle, dbSettings = null) {
	const isOriginal = lang.isOriginal || false;
	const langCode = lang.code || 'en';

	// Use database settings if available, otherwise fallback to constants
	let seoData;
	if (dbSettings && dbSettings[langCode]) {
		seoData = dbSettings[langCode];
	} else {
		seoData = SEO_DATA[langCode] || SEO_DATA['en'];
	}

	// Title - use database translation_suffix if available
	const titleSuffix = isOriginal
		? (seoData.original_suffix || SEO_DATA['en'].original_suffix)
		: (seoData.translation_suffix || seoData.original_suffix || (lang.name + ' Translation'));
	const title = postTitle ? `${postTitle} | ${titleSuffix}` : `Song Title | ${titleSuffix}`;

	// Description - use database translation_meta_suffix if available
	const metaSuffix = isOriginal
		? (seoData.meta_suffix || SEO_DATA['en'].meta_suffix)
		: (seoData.translation_meta_suffix || seoData.meta_suffix || SEO_DATA['en'].meta_suffix);
	const description = postTitle
		? `${postTitle} - ${metaSuffix}`
		: `Song Title - ${metaSuffix}`;

	return { title, description };
}
