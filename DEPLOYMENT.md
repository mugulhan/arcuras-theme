# Arcuras Theme v2.0.0 - Deployment Guide

## 🚨 MAJOR UPDATE - Custom Post Type Migration

This version migrates from standard `post` to custom `lyrics` post type. **Manual data migration required!**

---

## 📋 Pre-Deployment Checklist

### On Local/Staging Environment:

1. **Export Lyrics Data:**
   - Go to: `WordPress Admin → Tools → Arcuras Tools`
   - Click: **"Lyrics Export Et (JSON)"**
   - Save the downloaded JSON file (e.g., `lyrics-export-2025-10-25.json`)

2. **Verify Export:**
   - Check file size (should be large if you have many lyrics)
   - Open JSON file to verify data integrity

3. **Backup Database:**
   ```bash
   # Create database backup before migration
   mysqldump -u username -p database_name > backup_before_v2.sql
   ```

---

## 🚀 Deployment Steps

### 1. Upload Theme to Production Server

**Option A: Via WordPress Admin**
1. Zip the theme folder (excluding `node_modules`)
2. Upload via `Appearance → Themes → Add New → Upload Theme`

**Option B: Via FTP/SSH**
```bash
# On your local machine
cd /path/to/arcuras-dev/wordpress/wp-content/themes
zip -r arcuras-v2.0.0.zip arcuras -x "arcuras/node_modules/*"

# Upload via FTP to:
# /wp-content/themes/arcuras/
```

### 2. Activate Theme

1. Go to `Appearance → Themes`
2. Activate **Arcuras** theme
3. Verify homepage loads correctly

### 3. Import Lyrics Data

1. Go to: `WordPress Admin → Tools → Arcuras Tools`
2. Under **"Lyrics Import"** section:
   - Click **"Choose File"**
   - Select your exported JSON file
   - Click **"Lyrics Import Et"**
3. Wait for import to complete
4. Verify import results:
   - Imported: X lyrics
   - Skipped: X (duplicates)
   - Errors: X

### 4. Delete Old Posts (Optional)

⚠️ **WARNING: This action is irreversible!**

If you want to clean up old `post` type entries:

1. Go to: `WordPress Admin → Tools → Arcuras Tools`
2. Under **"Eski Post'ları Sil"** section:
   - Verify the count of posts to be deleted
   - Click **"Tüm Eski Post'ları Sil"**
   - Confirm the action

**Recommendation:** Keep old posts for a few days until you verify everything works correctly.

### 5. Verify Installation

Check the following:

- [ ] Homepage displays correctly
- [ ] Lyrics pages load properly
- [ ] Sidebar navigation works
- [ ] Profile page tabs function
- [ ] Saved Lines feature works
- [ ] Search functionality
- [ ] Taxonomies (Singer, Producer, Songwriter) display correctly
- [ ] Recent Views page shows lyrics
- [ ] Line Export/Save feature works

---

## 📊 Statistics Check

After deployment, verify data in `Tools → Arcuras Tools`:

```
Post (Eski Sistem):     0 yayınlanmış (if deleted)
Lyrics (Yeni Sistem):   XXX yayınlanmış ✓
Singers:                XXX şarkıcı
Producers:              XXX yapımcı
Songwriters:            XXX söz yazarı
```

---

## 🔧 Troubleshooting

### Issue: Lyrics not showing after import

**Solution:**
1. Check `Tools → Arcuras Tools` for import errors
2. Re-upload JSON file
3. Check file permissions: `chmod 755 /wp-content/themes/arcuras`

### Issue: Featured images not displaying

**Solution:**
- Images are stored as URLs in export
- If domain changed, update URLs in database:
  ```sql
  UPDATE wp_postmeta
  SET meta_value = REPLACE(meta_value, 'old-domain.com', 'new-domain.com')
  WHERE meta_key = '_thumbnail_id';
  ```

### Issue: Saved Lines feature not working

**Solution:**
1. Verify users are logged in
2. Check browser console for errors
3. Clear browser cache and WordPress cache

### Issue: Admin Tools page not showing

**Solution:**
1. Verify `inc/admin-tools.php` exists
2. Check `functions.php` includes the file:
   ```php
   require_once get_template_directory() . '/inc/admin-tools.php';
   ```

---

## 🔄 Rollback Plan

If something goes wrong:

1. **Restore Previous Theme:**
   - Activate previous theme version from `Appearance → Themes`

2. **Restore Database:**
   ```bash
   mysql -u username -p database_name < backup_before_v2.sql
   ```

3. **Contact Support:**
   - Email: support@mudosdigital.com
   - Include error logs from `wp-content/debug.log`

---

## 📝 New Features in v2.0.0

### User Features:
- ✨ Save individual lyric lines from any song
- 📊 View saved lines in profile with statistics
- 🎯 Better lyrics display spacing

### Admin Features:
- 🛠️ Admin Tools page for data management
- 📦 Export/Import lyrics with full metadata
- 🗑️ Bulk delete old posts
- 📊 Content statistics dashboard

### Technical:
- 🏗️ Custom `lyrics` post type (better structure)
- 🔧 Featured image fallback with SVG placeholder
- 🐛 Multiple bug fixes and improvements

---

## ⚙️ Configuration

No additional configuration needed. Theme works out of the box!

Optional settings available in:
- `Settings → Arcuras Theme Options` (if exists)
- `Appearance → Customize`

---

## 📞 Support

For issues or questions:
- Documentation: [Your docs URL]
- Support: support@mudosdigital.com
- GitHub: [Your GitHub URL]

---

**Last Updated:** October 25, 2025
**Theme Version:** 2.0.0
**WordPress Compatibility:** 6.0+
**PHP Compatibility:** 7.4+
