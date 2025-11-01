# Lyrics & Translations Block (React Version)

Gutenberg block for displaying song lyrics with multiple language translations.

## 🚀 React Migration

This block has been migrated from Vanilla JS to **React/JSX** for better maintainability and development experience.

### What Changed:
- ✅ Editor code rewritten in React/JSX
- ✅ Modern build system with `@wordpress/scripts`
- ✅ Modular component structure
- ❌ **NO database changes** - all existing songs work perfectly!
- ❌ **NO frontend changes** - users see exactly the same interface

### Backward Compatibility:
All existing lyrics blocks in the database continue to work **without any migration**. The block attributes structure remains identical.

## 📁 File Structure

```
lyrics-translations/
├── src/                          # Source files (React/JSX)
│   ├── index.js                  # Block registration
│   ├── constants.js              # Languages & SEO data
│   ├── utils.js                  # Helper functions
│   └── components/
│       ├── Edit.js               # Main editor component
│       └── NumberedTextarea.js   # Custom textarea with line numbers
├── build/                        # Compiled files (auto-generated)
│   ├── index.js                  # Bundled & minified JS
│   └── index.asset.php           # WordPress dependencies
├── frontend.js                   # Frontend interactivity (unchanged)
├── style.css                     # Frontend styles (unchanged)
├── editor.css                    # Editor styles (unchanged)
├── block.json                    # Block metadata
├── package.json                  # npm dependencies
└── editor.js.vanilla-backup      # Original vanilla JS (backup)
```

## 🛠️ Development

### Prerequisites:
- Node.js 16+ and npm

### Install Dependencies:
```bash
cd /path/to/lyrics-translations
npm install
```

### Development Mode (Auto-rebuild on save):
```bash
npm run start
```

### Production Build:
```bash
npm run build
```

### Other Commands:
```bash
npm run format       # Format code with Prettier
npm run lint:js      # Lint JavaScript
npm run lint:css     # Lint CSS
```

## 📝 Making Changes

1. Edit files in `src/` directory
2. Run `npm run start` for development (auto-rebuild)
3. OR run `npm run build` for production
4. Build files are created in `build/` directory
5. WordPress loads from `build/index.js`

**⚠️ Important:** Never edit `build/index.js` directly - it gets overwritten on build!

## 🎯 Features

- Multi-language lyrics support (28 languages)
- Line-by-line translations
- Auto-detection or manual section markers ([Verse 1], [Chorus], etc.)
- Section comments (clickable explanations)
- SEO preview for each language
- Line count validation
- Structure synchronization between languages
- Custom language support

## 🔄 Deployment

Only deploy the following files to production:
- `build/` directory (compiled JS)
- `frontend.js`
- `style.css`
- `editor.css`
- `block.json`

**DO NOT deploy:**
- `src/` directory
- `node_modules/`
- `package.json` / `package-lock.json`

## 🧪 Testing

After making changes:
1. Clear WordPress cache
2. Hard refresh browser (Cmd+Shift+R / Ctrl+Shift+F5)
3. Open existing lyrics post in editor
4. Verify all data loads correctly
5. Create new lyrics post
6. Test all features (add language, section detection, comments, etc.)

## 📊 Benefits of React Version

### Before (Vanilla JS):
- 1198 lines of code
- Difficult to read and maintain
- Hard to add new features
- No auto-complete in IDEs

### After (React/JSX):
- ~600 lines of code (50% reduction)
- Clean, readable JSX syntax
- Easy to add new features
- Full TypeScript/IntelliSense support

## 🐛 Troubleshooting

### Block doesn't appear in editor:
```bash
npm run build
# Then hard refresh browser
```

### Build errors:
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### "Cannot find module" error:
Check that all imports in `src/` files are correct.

## 📚 Resources

- [Gutenberg Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [@wordpress/scripts Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)
- [React Documentation](https://react.dev/)

---

**Last Updated:** October 29, 2025
**Version:** 2.3.0 (React)
