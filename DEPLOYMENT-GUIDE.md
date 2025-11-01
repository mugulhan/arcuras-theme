# Arcuras Theme Deployment Guide

## Quick Deployment

Yeni bir versiyon yayınlamak için sadece deploy scriptini çalıştır:

```bash
cd /Users/muhammetgulhan/Documents/arcuras-dev/wordpress/wp-content/themes/arcuras
./deploy.sh
```

Script sana şunları soracak:
1. **Yeni versiyon numarası** (örn: 2.13.3, 2.14.0)
2. **Changelog başlığı** (örn: "Bug Fixes & Improvements")
3. **Changelog maddeleri** (her satıra bir madde, bitince Ctrl+D)

## Script Otomatik Olarak Yapar:

### ✅ Step 1/8: Version güncelleme
- `style.css` dosyasındaki version numarasını günceller

### ✅ Step 2/8: Package.json güncelleme
- `blocks/lyrics-translations/package.json` version günceller

### ✅ Step 3/8: Block build
- `npm run build` çalıştırır (block JavaScript'i derler)

### ✅ Step 4/8: Git commit
- Tüm değişiklikleri commit eder (changelog ile birlikte)

### ✅ Step 5/8: Git push
- GitHub'a push eder

### ✅ Step 6/8: ZIP oluşturma
- Optimize edilmiş theme zip oluşturur (~1.1 MB)
- `node_modules` ve gereksiz dosyaları hariç tutar
- İki zip oluşturur: `arcuras.zip` ve `arcuras-vX.X.X.zip`

### ✅ Step 7/8: GitHub release
- GitHub'da yeni release oluşturur
- Changelog ve download linklerini ekler

### ✅ Step 8/8: ZIP upload
- Her iki zip dosyasını release'e yükler
- **Bu sayede WordPress otomatik güncelleme çalışır! 🎯**

## Sonra Ne Olur?

### Otomatik Güncelleme (Önerilen)
WordPress 5-10 dakika içinde yeni versiyonu algılar:
1. https://arcuras.com/wp-admin/update-core.php
2. Sayfayı yenile
3. "Update Now" butonuna tıkla

### Manuel Güncelleme
Eğer acilse:
```bash
# Sunucuda
wp theme install https://github.com/mugulhan/arcuras-theme/releases/download/vX.X.X/arcuras.zip --activate --force
```

## Önemli Notlar

### ✅ YAPILMASI GEREKENLER:
- Deploy scriptini kullan (`./deploy.sh`)
- Semantic versioning kullan (2.13.3, 2.14.0, 3.0.0)
- Anlamlı changelog yaz
- Test et localhost'ta deployment öncesi

### ❌ YAPILMAMASI GEREKENLER:
- Manuel version değiştirme (deploy.sh kullan!)
- Zip'i manuel oluşturma (script yapıyor!)
- Release'i zip olmadan yayınlama (WordPress görmez!)
- `node_modules` dahil zip oluşturma (17 MB olur!)

## Troubleshooting

### WordPress güncellemeyi görmüyor?
```bash
# Sunucuda cache temizle
wp transient delete update_themes --allow-root
```

### Zip dosyası çok büyük mü?
Script otomatik olarak optimize ediyor, `node_modules` hariç tutuyor.

### Deploy script hata veriyor?
1. GitHub CLI kurulu mu? `gh --version`
2. Git commit yapılacak değişiklik var mı?
3. npm install yapıldı mı? `cd blocks/lyrics-translations && npm install`

## Örnek Deployment Akışı

```bash
# 1. Kod değişikliklerini yap
# 2. Localhost'ta test et
# 3. Deploy scriptini çalıştır

$ cd /Users/muhammetgulhan/Documents/arcuras-dev/wordpress/wp-content/themes/arcuras
$ ./deploy.sh

🚀 Arcuras Theme Deployment Script

Current version: 2.13.2
Enter new version (e.g., 2.12.2): 2.13.3
Enter changelog title (e.g., 'Bug Fixes & Improvements'): Performance Improvements
Enter changelog items (one per line, press Ctrl+D when done):
Optimized database queries
Reduced CSS bundle size
Fixed memory leak in translation switcher
^D

📝 Summary:
  Version: 2.13.2 → 2.13.3
  Title: Performance Improvements
  Changes:
    - Optimized database queries
    - Reduced CSS bundle size
    - Fixed memory leak in translation switcher

Continue with deployment? (y/n): y

🔄 Step 1/8: Updating version in style.css...
✅ style.css updated

🔄 Step 2/8: Updating version in package.json...
✅ package.json updated

🔄 Step 3/8: Building block...
✅ Block built successfully

🔄 Step 4/8: Committing to git...
✅ Changes committed

🔄 Step 5/8: Pushing to GitHub...
✅ Pushed to GitHub

🔄 Step 6/8: Creating theme zip package...
✅ Theme zip created (1.1M)

🔄 Step 7/8: Creating GitHub release...
✅ GitHub release created

🔄 Step 8/8: Uploading theme zip to release...
✅ Theme zip uploaded to release

🎉 Deployment completed successfully!
Release URL: https://github.com/mugulhan/arcuras-theme/releases/tag/v2.13.3
Download: https://github.com/mugulhan/arcuras-theme/releases/download/v2.13.3/arcuras.zip

⚠️  WordPress will auto-detect update in ~12 hours, or update manually:
  1. Go to https://arcuras.com/wp-admin/themes.php
  2. Update Arcuras theme to v2.13.3 (or wait for auto-update)
```

## Version Numaralandırma

### Patch (2.13.2 → 2.13.3)
- Bug fix
- Küçük iyileştirmeler
- Typo düzeltmeleri

### Minor (2.13.3 → 2.14.0)
- Yeni özellikler
- Geriye uyumlu değişiklikler
- Yeni dil ekleme

### Major (2.14.0 → 3.0.0)
- Breaking changes
- Büyük yeniden yapılanma
- Geriye uyumsuz değişiklikler

## Veritabanı Güncellemeleri

Eğer veritabanı güncellemesi gerekliyse (SEO ayarları gibi):

```bash
# Localhost'ta test et
docker exec arcuras-dev-wordpress-1 wp option update arcuras_language_seo_settings --format=json "$(cat /tmp/updated_seo.json)" --allow-root

# Sunucuda uygula
wp option update arcuras_language_seo_settings --format=json "$(cat /tmp/updated_seo.json)" --allow-root
```

## Cache Temizleme

Tema güncellemesinden sonra:

**WordPress Admin Panel:**
https://arcuras.com/wp-admin/admin.php?page=arcuras-theme-settings&tab=tools
→ "🗑️ Clear All Caches" butonuna tıkla

**WP-CLI:**
```bash
wp transient delete --all --allow-root
wp cache flush --allow-root
```

---

**Son Güncelleme**: 1 Kasım 2025
**Script Versiyonu**: 1.0 (8 adımlı deployment)
