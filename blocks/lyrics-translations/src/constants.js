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
	{ value: 'kk', label: 'Kazakh (Қазақша)' },
	{ value: 'alt', label: 'Altai (Алтай тил)' },
	{ value: 'mn', label: 'Mongolian (Монгол)' }
];

/**
 * SEO Title/Description helpers
 */
export const SEO_DATA = {
	en: {
		original_suffix: 'Lyrics, Translations and Annotations',
		meta_suffix: 'Read lyrics, discover translations in multiple languages, and explore detailed annotations',
		translation_suffix: 'Lyrics, English Translation and Annotations',
		translation_meta_suffix: 'Read the English translation with original lyrics and detailed annotations'
	},
	es: {
		original_suffix: 'Letras, Traducciones y Anotaciones',
		meta_suffix: 'Lee las letras, descubre traducciones en varios idiomas y explora anotaciones detalladas',
		translation_suffix: 'Letras, Traducción al Español y Anotaciones',
		translation_meta_suffix: 'Lee la traducción al español con letras originales y anotaciones detalladas'
	},
	tr: {
		original_suffix: 'Şarkı Sözleri, Çeviriler ve Açıklamalar',
		meta_suffix: 'Şarkı sözlerini okuyun, birden fazla dilde çevirileri keşfedin ve detaylı açıklamaları inceleyin',
		translation_suffix: 'Şarkı Sözleri, Türkçe Çevirisi ve Açıklamaları',
		translation_meta_suffix: 'Orijinal şarkı sözleri ve açıklamalarla birlikte Türkçe çeviriyi okuyun'
	},
	de: {
		original_suffix: 'Liedtext, Übersetzungen und Anmerkungen',
		meta_suffix: 'Lesen Sie die Texte, entdecken Sie Übersetzungen in mehreren Sprachen und erkunden Sie detaillierte Anmerkungen',
		translation_suffix: 'Liedtext, Deutsche Übersetzung und Anmerkungen',
		translation_meta_suffix: 'Lesen Sie die deutsche Übersetzung mit Originaltext und detaillierten Anmerkungen'
	},
	fr: {
		original_suffix: 'Paroles, Traductions et Annotations',
		meta_suffix: 'Lisez les paroles, découvrez les traductions en plusieurs langues et explorez les annotations détaillées',
		translation_suffix: 'Paroles, Traduction Française et Annotations',
		translation_meta_suffix: 'Lisez la traduction française avec les paroles originales et des annotations détaillées'
	},
	ar: {
		original_suffix: 'كلمات الأغنية والترجمات والتعليقات',
		meta_suffix: 'اقرأ الكلمات، اكتشف الترجمات بعدة لغات، واستكشف التعليقات التفصيلية',
		translation_suffix: 'كلمات الأغنية، الترجمة العربية والتعليقات',
		translation_meta_suffix: 'اقرأ الترجمة العربية مع الكلمات الأصلية والتعليقات التفصيلية'
	},
	it: {
		original_suffix: 'Testo, Traduzioni e Annotazioni',
		meta_suffix: 'Leggi i testi, scopri le traduzioni in più lingue ed esplora le annotazioni dettagliate',
		translation_suffix: 'Testo, Traduzione Italiana e Annotazioni',
		translation_meta_suffix: 'Leggi la traduzione italiana con il testo originale e annotazioni dettagliate'
	},
	pt: {
		original_suffix: 'Letras, Traduções e Anotações',
		meta_suffix: 'Leia as letras, descubra traduções em vários idiomas e explore anotações detalhadas',
		translation_suffix: 'Letras, Tradução em Português e Anotações',
		translation_meta_suffix: 'Leia a tradução em português com letras originais e anotações detalhadas'
	},
	ru: {
		original_suffix: 'Текст песни, переводы и комментарии',
		meta_suffix: 'Читайте тексты, открывайте переводы на несколько языков и изучайте подробные комментарии',
		translation_suffix: 'Текст песни, русский перевод и комментарии',
		translation_meta_suffix: 'Читайте русский перевод с оригинальным текстом и подробными комментариями'
	},
	ja: {
		original_suffix: '歌詞、翻訳、注釈',
		meta_suffix: '歌詞を読み、複数言語の翻訳を発見し、詳細な注釈を探索してください',
		translation_suffix: '歌詞、日本語翻訳と注釈',
		translation_meta_suffix: 'オリジナルの歌詞と詳細な注釈付きの日本語翻訳を読む'
	},
	ko: {
		original_suffix: '가사, 번역 및 주석',
		meta_suffix: '가사를 읽고, 여러 언어로 된 번역을 발견하고, 자세한 주석을 살펴보세요',
		translation_suffix: '가사, 한국어 번역 및 주석',
		translation_meta_suffix: '원본 가사와 상세한 주석이 포함된 한국어 번역을 읽어보세요'
	},
	zh: {
		original_suffix: '歌词、翻译和注释',
		meta_suffix: '阅读歌词，发现多语言翻译，探索详细注释',
		translation_suffix: '歌词、中文翻译和注释',
		translation_meta_suffix: '阅读带有原文歌词和详细注释的中文翻译'
	},
	hi: {
		original_suffix: 'गीत, अनुवाद और टिप्पणियाँ',
		meta_suffix: 'गीत पढ़ें, कई भाषाओं में अनुवाद खोजें और विस्तृत टिप्पणियों का अन्वेषण करें',
		translation_suffix: 'गीत, हिंदी अनुवाद और टिप्पणियाँ',
		translation_meta_suffix: 'मूल गीत और विस्तृत टिप्पणियों के साथ हिंदी अनुवाद पढ़ें'
	},
	nl: {
		original_suffix: 'Songteksten, Vertalingen en Annotaties',
		meta_suffix: 'Lees songteksten, ontdek vertalingen in meerdere talen en verken gedetailleerde annotaties',
		translation_suffix: 'Songteksten, Nederlandse Vertaling en Annotaties',
		translation_meta_suffix: 'Lees de Nederlandse vertaling met originele songtekst en gedetailleerde annotaties'
	},
	pl: {
		original_suffix: 'Teksty Piosenek, Tłumaczenia i Adnotacje',
		meta_suffix: 'Czytaj teksty, odkrywaj tłumaczenia w wielu językach i przeglądaj szczegółowe adnotacje',
		translation_suffix: 'Teksty Piosenek, Polskie Tłumaczenie i Adnotacje',
		translation_meta_suffix: 'Przeczytaj polskie tłumaczenie z oryginalnym tekstem i szczegółowymi adnotacjami'
	},
	sv: {
		original_suffix: 'Texter, Översättningar och Kommentarer',
		meta_suffix: 'Läs texter, upptäck översättningar på flera språk och utforska detaljerade kommentarer',
		translation_suffix: 'Texter, Svensk Översättning och Kommentarer',
		translation_meta_suffix: 'Läs den svenska översättningen med originaltext och detaljerade kommentarer'
	},
	no: {
		original_suffix: 'Tekster, Oversettelser og Merknader',
		meta_suffix: 'Les tekster, oppdag oversettelser på flere språk og utforsk detaljerte merknader',
		translation_suffix: 'Tekster, Norsk Oversettelse og Merknader',
		translation_meta_suffix: 'Les den norske oversettelsen med originaltekst og detaljerte merknader'
	},
	da: {
		original_suffix: 'Tekster, Oversættelser og Annotationer',
		meta_suffix: 'Læs tekster, opdag oversættelser på flere sprog og udforsk detaljerede annotationer',
		translation_suffix: 'Tekster, Dansk Oversættelse og Annotationer',
		translation_meta_suffix: 'Læs den danske oversættelse med originaltekst og detaljerede annotationer'
	},
	fi: {
		original_suffix: 'Sanoitukset, Käännökset ja Huomautukset',
		meta_suffix: 'Lue sanoituksia, löydä käännöksiä useilla kielillä ja tutustu yksityiskohtaisiin huomautuksiin',
		translation_suffix: 'Sanoitukset, Suomenkielinen Käännös ja Huomautukset',
		translation_meta_suffix: 'Lue suomenkielinen käännös alkuperäisten sanoitusten ja yksityiskohtaisten huomautusten kanssa'
	},
	el: {
		original_suffix: 'Στίχοι, Μεταφράσεις και Σημειώσεις',
		meta_suffix: 'Διαβάστε στίχους, ανακαλύψτε μεταφράσεις σε πολλές γλώσσες και εξερευνήστε λεπτομερείς σημειώσεις',
		translation_suffix: 'Στίχοι, Ελληνική Μετάφραση και Σημειώσεις',
		translation_meta_suffix: 'Διαβάστε την ελληνική μετάφραση με τους αρχικούς στίχους και λεπτομερείς σημειώσεις'
	},
	he: {
		original_suffix: 'מילים, תרגומים והערות',
		meta_suffix: 'קרא מילים, גלה תרגומים במספר שפות וחקור הערות מפורטות',
		translation_suffix: 'מילים, תרגום לעברית והערות',
		translation_meta_suffix: 'קרא את התרגום לעברית עם המילים המקוריות והערות מפורטות'
	},
	uk: {
		original_suffix: 'Тексти пісень, переклади та коментарі',
		meta_suffix: 'Читайте тексти, відкривайте переклади кількома мовами та вивчайте детальні коментарі',
		translation_suffix: 'Тексти пісень, український переклад та коментарі',
		translation_meta_suffix: 'Читайте український переклад з оригінальним текстом та детальними коментарями'
	},
	cs: {
		original_suffix: 'Texty, Překlady a Poznámky',
		meta_suffix: 'Čtěte texty, objevujte překlady v několika jazycích a prozkoumávejte podrobné poznámky',
		translation_suffix: 'Texty, Český Překlad a Poznámky',
		translation_meta_suffix: 'Přečtěte si český překlad s původním textem a podrobnými poznámkami'
	},
	ro: {
		original_suffix: 'Versuri, Traduceri și Adnotări',
		meta_suffix: 'Citește versuri, descoperă traduceri în mai multe limbi și explorează adnotări detaliate',
		translation_suffix: 'Versuri, Traducere în Română și Adnotări',
		translation_meta_suffix: 'Citește traducerea în română cu versurile originale și adnotări detaliate'
	},
	hu: {
		original_suffix: 'Dalszövegek, Fordítások és Megjegyzések',
		meta_suffix: 'Olvasson dalszövegeket, fedezzen fel fordításokat több nyelven és fedezze fel a részletes megjegyzéseket',
		translation_suffix: 'Dalszövegek, Magyar Fordítás és Megjegyzések',
		translation_meta_suffix: 'Olvasd el a magyar fordítást az eredeti szöveggel és részletes megjegyzésekkel'
	},
	th: {
		original_suffix: 'เนื้อเพลง, การแปล และคำอธิบาย',
		meta_suffix: 'อ่านเนื้อเพลง ค้นพบการแปลหลายภาษา และสำรวจคำอธิบายโดยละเอียด',
		translation_suffix: 'เนื้อเพลง, แปลภาษาไทย และคำอธิบาย',
		translation_meta_suffix: 'อ่านคำแปลภาษาไทยพร้อมเนื้อเพลงต้นฉบับและคำอธิบายโดยละเอียด'
	},
	vi: {
		original_suffix: 'Lời bài hát, Bản dịch và Chú thích',
		meta_suffix: 'Đọc lời bài hát, khám phá bản dịch bằng nhiều ngôn ngữ và khám phá chú thích chi tiết',
		translation_suffix: 'Lời bài hát, Bản dịch Tiếng Việt và Chú thích',
		translation_meta_suffix: 'Đọc bản dịch tiếng Việt với lời bài hát gốc và chú thích chi tiết'
	},
	id: {
		original_suffix: 'Lirik, Terjemahan dan Anotasi',
		meta_suffix: 'Baca lirik, temukan terjemahan dalam berbagai bahasa dan jelajahi anotasi terperinci',
		translation_suffix: 'Lirik, Terjemahan Bahasa Indonesia dan Anotasi',
		translation_meta_suffix: 'Baca terjemahan bahasa Indonesia dengan lirik asli dan anotasi terperinci'
	},
	ba: {
		original_suffix: 'Йыр һүҙҙәре, тәржемәләр һәм аңлатмалар',
		meta_suffix: 'Йыр һүҙҙәрен уҡығыҙ, төрлө телдәрҙәге тәржемәләрҙе табығыҙ һәм тулы аңлатмаларҙы өйрәнегеҙ',
		translation_suffix: 'Йыр һүҙҙәре, башҡорт тәржемәһе һәм аңлатмалар',
		translation_meta_suffix: 'Башҡорт тәржемәһен төп текст һәм тулы аңлатмалар менән уҡығыҙ'
	},
	az: {
		original_suffix: 'Mahnı sözləri, Tərcümələr və Açıqlamalar',
		meta_suffix: 'Mahnı sözlərini oxuyun, müxtəlif dillərdə tərcümələri kəşf edin və ətraflı açıqlamaları araşdırın',
		translation_suffix: 'Mahnı sözləri, Azərbaycan dilinə tərcümə və açıqlamalar',
		translation_meta_suffix: 'Orijinal mətn və ətraflı açıqlamalarla birlikdə Azərbaycan dilinə tərcüməni oxuyun'
	},
	kk: {
		original_suffix: 'Ән сөздері, Аудармалар және Түсініктемелер',
		meta_suffix: 'Ән сөздерін оқыңыз, әртүрлі тілдердегі аудармаларды табыңыз және толық түсініктемелерді зерттеңіз',
		translation_suffix: 'Ән сөздері, қазақ тіліне аударма және түсініктемелер',
		translation_meta_suffix: 'Түпнұсқа мәтін және толық түсініктемелермен бірге қазақ тіліндегі аударманы оқыңыз'
	},
	alt: {
		original_suffix: 'Кожоҥ сӧстӧри, Которгон сӧстӧр ла Туружылар',
		meta_suffix: 'Кожоҥ сӧстӧрин окуп, туужы тилдерде которгон сӧстӧрди табар, ла туружыларды ӧргӧнӧр',
		translation_suffix: 'Кожоҥ сӧстӧри, алтай тилге которгон сӧс ла туружылар',
		translation_meta_suffix: 'Баштапкы текст ла толук туружылар ла бӧлӧ алтай тилге которгон сӧсти окуп'
	},
	mn: {
		original_suffix: 'Дууны үг, Орчуулга ба Тайлбар',
		meta_suffix: 'Дууны үг уншиж, олон хэл дээрх орчуулга олж, дэлгэрэнгүй тайлбар судлаарай',
		translation_suffix: 'Дууны үг, монгол орчуулга ба тайлбар',
		translation_meta_suffix: 'Эх үг болон дэлгэрэнгүй тайлбартай монгол орчуулгыг уншина уу'
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
