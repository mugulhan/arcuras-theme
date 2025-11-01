# Section Header Component

## Amaç
Tüm sayfalardaki bölüm başlıklarını standartlaştırmak için yeniden kullanılabilir component.

## Dosya Konumu
`template-parts/components/section-header.php`

## Kullanım

```php
<?php
// Minimum kullanım (sadece başlık)
set_query_var('section_title', __('Featured Lyrics', 'gufte'));
set_query_var('section_icon', '');
set_query_var('section_link_url', '');
get_template_part('template-parts/components/section-header');
?>
```

```php
<?php
// Icon ile birlikte
set_query_var('section_title', __('Latest Lyrics', 'gufte'));
set_query_var('section_icon', 'clock');
set_query_var('section_link_url', '');
get_template_part('template-parts/components/section-header');
?>
```

```php
<?php
// "View All" linki ile birlikte
set_query_var('section_title', __('Latest Lyrics', 'gufte'));
set_query_var('section_icon', 'clock');
set_query_var('section_link_url', home_url('/lyrics/'));
set_query_var('section_link_text', __('View All', 'gufte'));
get_template_part('template-parts/components/section-header');
?>
```

## Parametreler

| Parametre | Tip | Zorunlu | Varsayılan | Açıklama |
|-----------|-----|---------|------------|----------|
| `section_title` | string | ✅ | - | Bölüm başlığı |
| `section_icon` | string | ❌ | '' | gufte_icon() fonksiyonundan icon adı (örn: 'clock', 'translate', 'music-note') |
| `section_link_url` | string | ❌ | '' | "View All" butonu için URL |
| `section_link_text` | string | ❌ | 'View All' | Link butonu metni |
| `section_header_classes` | string | ❌ | '' | Ek CSS class'ları |

## Mevcut Icon'lar

Component `gufte_icon()` fonksiyonunu kullanır. Kullanılabilir icon'lar:

- `clock` - Saat ikonu (Latest Lyrics için)
- `translate` - Çeviri ikonu (Translated Lyrics için)
- `music-note` - Müzik notu ikonu (Original Languages için)
- `file-document` - Doküman ikonu
- `arrow-right` - Sağ ok ikonu (View All butonunda otomatik kullanılır)

Tüm icon listesi için `inc/utilities/inline-icons.php` dosyasına bakın.

## Stil Özellikleri

Component aşağıdaki standart stilleri kullanır:

- **Başlık boyutu:** `text-2xl md:text-3xl` (mobilde 1.5rem, masaüstünde 1.875rem)
- **Renk:** `text-gray-900` (koyu gri)
- **Font ağırlığı:** `font-bold`
- **Icon boyutu:** `w-6 h-6 md:w-8 md:h-8` (mobilde 24px, masaüstünde 32px)
- **Icon rengi:** `text-primary-600`
- **Layout:** Flexbox ile justify-between (başlık solda, link sağda)
- **Alt boşluk:** `mb-4 md:mb-6`

## Responsive Davranış

- **Mobil (< 768px):**
  - Başlık: 1.5rem (text-2xl)
  - Icon: 24px (w-6 h-6)
  - Link padding: px-3 py-2
  - Font size: text-xs

- **Desktop (≥ 768px):**
  - Başlık: 1.875rem (text-3xl)
  - Icon: 32px (w-8 h-8)
  - Link padding: px-4 py-2.5
  - Font size: text-sm

## Kullanım Örnekleri

### index.php (Anasayfa)
Aşağıdaki bölümler standartlaştırıldı:

1. **Featured Lyrics** - Icon yok, link yok
2. **Explore Lyrics by Genre** - Icon yok, link yok
3. **Original Languages** - Music note icon, link yok
4. **Translated Lyrics** - Translate icon, link yok
5. **Latest Lyrics** - Clock icon, View All link var

### template-functions.php
`arcuras_genre_grid()` fonksiyonu içinde kullanıldı.

## Gelecek Geliştirmeler

Bu component şu dosyalarda da kullanılabilir:
- taxonomy-original_language.php
- taxonomy-translated_language.php
- archive.php
- search.php
- Diğer arşiv ve sayfa template'leri

## Notlar

- Component `get_query_var()` kullanarak parametreleri alır
- Başlık boşsa (empty) component hiçbir şey çıktılamaz
- Link URL boşsa "View All" butonu gösterilmez
- Icon boşsa icon gösterilmez
- Tüm metinler çeviri fonksiyonu ile sarılmalı: `__('text', 'gufte')`
