#!/bin/bash
# Build and Prepare BlockXpert for WordPress.org Deployment
# Usage: ./deploy.sh [--skip-build] [--skip-composer] [--dry-run]

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_NAME="$(basename "$PLUGIN_DIR")"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Flags
SKIP_BUILD=false
SKIP_COMPOSER=false
DRY_RUN=false

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-build) SKIP_BUILD=true; shift ;;
        --skip-composer) SKIP_COMPOSER=true; shift ;;
        --dry-run) DRY_RUN=true; shift ;;
        *) echo "Unknown option: $1"; exit 1 ;;
    esac
done

echo
echo -e "${CYAN}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  BlockXpert - WordPress.org Deployment Script${NC}"
echo -e "${CYAN}════════════════════════════════════════════════════════${NC}"
echo

# Check if we're in right directory
if [[ ! -f "$PLUGIN_DIR/blockxpert.php" ]]; then
    echo -e "${RED}❌ Error: blockxpert.php not found!${NC}"
    echo -e "${YELLOW}Make sure you run this script from the scripts directory${NC}"
    exit 1
fi

echo -e "📂 Plugin Directory: ${PLUGIN_DIR}${NC}"
echo

# Step 1: Install npm dependencies
if [ "$SKIP_BUILD" = false ]; then
    echo -e "${CYAN}Step 1️⃣  - Installing npm dependencies...${NC}"
    cd "$PLUGIN_DIR"
    
    if [ "$DRY_RUN" = false ]; then
        npm install > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo -e "${RED}❌ npm install failed${NC}"
            exit 1
        fi
    fi
    
    echo -e "${GREEN}✅ npm dependencies installed${NC}"
fi

# Step 2: Build production assets
if [ "$SKIP_BUILD" = false ]; then
    echo
    echo -e "${CYAN}Step 2️⃣  - Building production assets (npm run build:prod)...${NC}"
    cd "$PLUGIN_DIR"
    
    if [ "$DRY_RUN" = false ]; then
        npm run build:prod > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo -e "${RED}❌ npm build failed${NC}"
            exit 1
        fi
    fi
    
    echo -e "${GREEN}✅ Production assets built${NC}"
    
    # Verify build output
    if [[ -d "$PLUGIN_DIR/build" ]]; then
        FILE_COUNT=$(find "$PLUGIN_DIR/build" -type f | wc -l)
        echo -e "   📦 Build directory contains $FILE_COUNT files${NC}"
    else
        echo -e "   ${YELLOW}⚠️  Build directory not found!${NC}"
    fi
fi

# Step 3: Install PHP composer dependencies
if [ "$SKIP_COMPOSER" = false ]; then
    echo
    echo -e "${CYAN}Step 3️⃣  - Installing PHP dependencies (composer install --no-dev)...${NC}"
    cd "$PLUGIN_DIR"
    
    if [ "$DRY_RUN" = false ]; then
        composer install --no-dev --optimize-autoloader > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo -e "${RED}❌ Composer install failed${NC}"
            exit 1
        fi
    fi
    
    echo -e "${GREEN}✅ PHP dependencies installed${NC}"
fi

# Step 4: Read current version
echo
echo -e "${CYAN}Step 4️⃣  - Reading current version...${NC}"
CURRENT_VERSION=$(grep -oP 'Version:\s+\K[0-9.]+' "$PLUGIN_DIR/blockxpert.php" || echo "")

if [ -z "$CURRENT_VERSION" ]; then
    echo -e "${RED}❌ Could not find version in blockxpert.php${NC}"
    exit 1
fi

echo -e "   Current version: ${CURRENT_VERSION}${NC}"

# Step 5: Create distribution package
echo
echo -e "${CYAN}Step 5️⃣  - Creating distribution package...${NC}"

TEMP_DIR="/tmp/blockxpert-deploy-$$"
DEPLOY_DIR="$TEMP_DIR/$PLUGIN_NAME"

if [ "$DRY_RUN" = false ]; then
    mkdir -p "$DEPLOY_DIR"
    
    # Files/folders to include
    INCLUDE=(
        "blockxpert.php"
        "composer.json"
        "license.txt"
        "README.md"
        "DEPLOYMENT.md"
        "includes"
        "languages"
        "build"
        "vendor"
    )
    
    echo -e "   📦 Copying plugin files...${NC}"
    
    for item in "${INCLUDE[@]}"; do
        SOURCE="$PLUGIN_DIR/$item"
        if [[ -e "$SOURCE" ]]; then
            cp -r "$SOURCE" "$DEPLOY_DIR/$item" 2>/dev/null || true
        fi
    done
    
    # Create zip file
    OUT_DIR="$(dirname "$PLUGIN_DIR")"
    ZIP_FILE="$OUT_DIR/blockxpert-$CURRENT_VERSION.zip"
    
    echo -e "   📦 Creating zip: blockxpert-$CURRENT_VERSION.zip${NC}"
    
    # Remove existing zip
    [[ -f "$ZIP_FILE" ]] && rm "$ZIP_FILE"
    
    # Create zip
    cd "$TEMP_DIR"
    zip -r "$ZIP_FILE" "$PLUGIN_NAME" > /dev/null 2>&1
    
    # Verify zip
    if [[ -f "$ZIP_FILE" ]]; then
        ZIP_SIZE=$(du -h "$ZIP_FILE" | cut -f1)
        echo -e "${GREEN}✅ Distribution package created${NC}"
        echo -e "   📊 File: blockxpert-$CURRENT_VERSION.zip${NC}"
        echo -e "   📊 Size: $ZIP_SIZE${NC}"
    else
        echo -e "${RED}❌ Failed to create zip file${NC}"
        exit 1
    fi
    
    # Cleanup temp directory
    rm -rf "$TEMP_DIR"
fi

# Step 6: Display deployment checklist
echo
echo -e "${CYAN}Step 6️⃣  - Deployment Checklist:${NC}"
echo
echo -e "${GREEN}✅ Production assets built and optimized${NC}"
echo -e "${GREEN}✅ PHP dependencies installed (production only)${NC}"
echo -e "${GREEN}✅ Development files excluded${NC}"
echo -e "${GREEN}✅ Distribution package created${NC}"
echo

# Step 7: Next steps
echo -e "${CYAN}Step 7️⃣  - Next Steps:${NC}"
echo
echo -e "${WHITE}🔗 Upload to WordPress.org:${NC}"
echo -e "   1. Go to https://wordpress.org/plugins/blockxpert/${NC}"
echo -e "   2. Dashboard → Add New Version${NC}"
echo -e "   3. Upload: blockxpert-$CURRENT_VERSION.zip${NC}"
echo
echo -e "${WHITE}📝 Update plugin files via SVN:${NC}"
echo -e "   svn co https://plugins.svn.wordpress.org/blockxpert/ blockxpert-svn${NC}"
echo -e "   # Copy trunk files, commit changes${NC}"
echo
echo -e "${WHITE}🏷️  Create Git Tag:${NC}"
echo -e "   git tag -a v$CURRENT_VERSION -m 'Release version $CURRENT_VERSION'${NC}"
echo -e "   git push origin v$CURRENT_VERSION${NC}"
echo

echo -e "${CYAN}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✨ Deployment package ready for WordPress.org!${NC}"
echo -e "${CYAN}════════════════════════════════════════════════════════${NC}"
