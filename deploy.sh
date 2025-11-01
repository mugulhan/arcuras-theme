#!/bin/bash

# Arcuras Theme Deployment Script
# This script automates the version update and GitHub release process
#
# IMPORTANT: Theme uses Plugin Update Checker with GitHub Releases
# - functions.php MUST use: $arcurasUpdateChecker->getVcsApi()->enableReleaseAssets();
# - DO NOT use: $arcurasUpdateChecker->setBranch('main');
# - WordPress will fetch updates from GitHub Releases, not from main branch
# - Release assets (arcuras.zip) are required for automatic updates

set -e  # Exit on error

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Arcuras Theme Deployment Script${NC}"
echo ""

# Get current version from style.css
CURRENT_VERSION=$(grep -m 1 "^Version:" style.css | sed 's/Version: //')
echo -e "${YELLOW}Current version: ${CURRENT_VERSION}${NC}"

# Ask for new version
read -p "Enter new version (e.g., 2.12.2): " NEW_VERSION

if [ -z "$NEW_VERSION" ]; then
    echo -e "${RED}❌ Version cannot be empty!${NC}"
    exit 1
fi

# Ask for changelog
echo ""
echo -e "${YELLOW}Enter changelog title (e.g., 'Bug Fixes & Improvements'):${NC}"
read CHANGELOG_TITLE

echo ""
echo -e "${YELLOW}Enter changelog items (one per line, press Ctrl+D when done):${NC}"
CHANGELOG_ITEMS=""
while IFS= read -r line; do
    if [ ! -z "$line" ]; then
        CHANGELOG_ITEMS="${CHANGELOG_ITEMS}- ${line}\n"
    fi
done

if [ -z "$CHANGELOG_ITEMS" ]; then
    echo -e "${RED}❌ Changelog cannot be empty!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}📝 Summary:${NC}"
echo -e "  Version: ${CURRENT_VERSION} → ${NEW_VERSION}"
echo -e "  Title: ${CHANGELOG_TITLE}"
echo -e "  Changes:"
echo -e "${CHANGELOG_ITEMS}" | sed 's/^/    /'
echo ""

read -p "Continue with deployment? (y/n): " CONFIRM
if [ "$CONFIRM" != "y" ]; then
    echo -e "${RED}❌ Deployment cancelled${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}🔄 Step 1/6: Updating version in style.css...${NC}"
sed -i '' "s/^Version: ${CURRENT_VERSION}/Version: ${NEW_VERSION}/" style.css

# Prepare changelog entry for style.css
CHANGELOG_ENTRY="v${NEW_VERSION} - ${CHANGELOG_TITLE}:\n${CHANGELOG_ITEMS}\n"
# Insert after "Changelog:" line
sed -i '' "/^Changelog:/a\\
${CHANGELOG_ENTRY}
" style.css

echo -e "${GREEN}✅ style.css updated${NC}"

echo ""
echo -e "${BLUE}🔄 Step 2/8: Updating GUFTE_VERSION in functions.php...${NC}"
sed -i '' "s/define('GUFTE_VERSION', '${CURRENT_VERSION}')/define('GUFTE_VERSION', '${NEW_VERSION}')/" functions.php
echo -e "${GREEN}✅ functions.php updated${NC}"

echo ""
echo -e "${BLUE}🔄 Step 3/8: Updating version in package.json...${NC}"
sed -i '' "s/\"version\": \"${CURRENT_VERSION}\"/\"version\": \"${NEW_VERSION}\"/" blocks/lyrics-translations/package.json
echo -e "${GREEN}✅ package.json updated${NC}"

echo ""
echo -e "${BLUE}🔄 Step 4/8: Building block...${NC}"
cd blocks/lyrics-translations
npm run build
cd ../..
echo -e "${GREEN}✅ Block built successfully${NC}"

echo ""
echo -e "${BLUE}🔄 Step 5/8: Committing to git...${NC}"
git add -A
git commit -m "v${NEW_VERSION} - ${CHANGELOG_TITLE}

${CHANGELOG_ITEMS}
🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
echo -e "${GREEN}✅ Changes committed${NC}"

echo ""
echo -e "${BLUE}🔄 Step 6/8: Pushing to GitHub...${NC}"
git push origin main
echo -e "${GREEN}✅ Pushed to GitHub${NC}"

echo ""
echo -e "${BLUE}🔄 Step 7/8: Creating theme zip package...${NC}"
cd ..
rm -f arcuras.zip arcuras-v${NEW_VERSION}.zip
zip -r arcuras-v${NEW_VERSION}.zip arcuras \
  -x "arcuras/.git/*" \
  -x "arcuras/node_modules/*" \
  -x "arcuras/.DS_Store" \
  -x "arcuras/blocks/*/node_modules/*" \
  -x "arcuras/build/node_modules/*" \
  -x "arcuras/.gitignore" \
  -x "arcuras/package-lock.json" \
  -x "arcuras/blocks/*/package-lock.json" \
  > /dev/null
cp arcuras-v${NEW_VERSION}.zip arcuras.zip
cd arcuras
echo -e "${GREEN}✅ Theme zip created ($(du -h ../arcuras.zip | cut -f1))${NC}"

echo ""
echo -e "${BLUE}🔄 Step 8/9: Creating GitHub release...${NC}"
gh release create "v${NEW_VERSION}" \
    --title "v${NEW_VERSION} - ${CHANGELOG_TITLE}" \
    --notes "**${CHANGELOG_TITLE}**

${CHANGELOG_ITEMS}
**Installation:**
Download \`arcuras.zip\` and upload to WordPress via Appearance > Themes > Add New > Upload Theme

**Full Changelog**: https://github.com/mugulhan/arcuras-theme/compare/v${CURRENT_VERSION}...v${NEW_VERSION}"
echo -e "${GREEN}✅ GitHub release created${NC}"

echo ""
echo -e "${BLUE}🔄 Step 9/9: Uploading theme zip to release...${NC}"
gh release upload "v${NEW_VERSION}" ../arcuras.zip ../arcuras-v${NEW_VERSION}.zip --clobber
echo -e "${GREEN}✅ Theme zip uploaded to release${NC}"

echo ""
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${BLUE}Release URL: https://github.com/mugulhan/arcuras-theme/releases/tag/v${NEW_VERSION}${NC}"
echo -e "${BLUE}Download: https://github.com/mugulhan/arcuras-theme/releases/download/v${NEW_VERSION}/arcuras.zip${NC}"
echo ""
echo -e "${YELLOW}⚠️  WordPress will auto-detect update in ~12 hours, or update manually:${NC}"
echo -e "  1. Go to https://arcuras.com/wp-admin/themes.php"
echo -e "  2. Update Arcuras theme to v${NEW_VERSION} (or wait for auto-update)"
echo -e "  3. Or download: wget https://github.com/mugulhan/arcuras-theme/releases/download/v${NEW_VERSION}/arcuras.zip"
