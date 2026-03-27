# 🗂️ BlockXpert Folder Structure Quick Reference

## 📊 Visual Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    WordPress Plugin: BlockXpert             │
└─────────────────────────────────────────────────────────────┘
         │
         ├─ 🔧 Plugin Entry: blockxpert.php
         │   └─ Loads all interfaces, services, classes
         │
         ├─ ⚙️  Configuration Files
         │   ├─ package.json (npm, build scripts)
         │   ├─ composer.json (PHP dependencies)
         │   └─ webpack.config.js (build system)
         │
         ├─ 📦 BACKEND (PHP) - includes/
         │   ├─ classes/                 ← Business logic
         │   │   ├─ Service Container (DI)
         │   │   ├─ Block Manager
         │   │   ├─ Logger, Cache, REST API
         │   │   └─ OpenAI Provider
         │   │
         │   ├─ interfaces/              ← Service contracts
         │   │   └─ 5 PHP interfaces (contracts for services)
         │   │
         │   ├─ admin/                   ← WordPress admin UI
         │   │   ├─ Settings page
         │   │   └─ Settings handler
         │   │
         │   └─ assets/                  ← Admin assets
         │       ├─ css/
         │       └─ js/
         │
         ├─ 🎨 FRONTEND (JavaScript/React) - src/
         │   ├─ blocks/                  ← 8 Gutenberg blocks
         │   │   ├─ text-animation/    (NEW: GSAP animations)
         │   │   ├─ post-grid/         (Complex layouts)
         │   │   ├─ product-slider/    (Carousel)
         │   │   ├─ product-carousel/
         │   │   ├─ ai-faq/            (Accordion)
         │   │   ├─ ai-product-recommendations/
         │   │   ├─ ai-recommendations/
         │   │   └─ advanced-post-block/
         │   │
         │   └─ shared/                  ← Reusable code
         │       ├─ components/          (React components)
         │       ├─ hooks/               (Custom React hooks)
         │       ├─ services/            (API clients)
         │       ├─ context/             (Global state)
         │       └─ utils/               (Helper functions)
         │
         ├─ 🔨 BUILD OUTPUT - build/
         │   ├─ Common chunks         (shared JS/CSS)
         │   ├─ Vendor chunk          (node_modules bundled)
         │   ├─ Runtime chunk
         │   └─ blocks/               (48 compiled assets)
         │       └─ [block-name]/     ← Compiled JS, CSS, metadata
         │
         ├─ 📚 Dependencies
         │   ├─ vendor/               (Composer - PHP)
         │   └─ node_modules/         (npm - JavaScript)
         │
         ├─ 🌐 Translations - languages/
         │   └─ blockxpert.pot        (Translation template)
         │
         └─ 🛠️  Build Tools - scripts/
             └─ build.js, validators, loggers
```

---

## 📍 Finding Things in BlockXpert

### "I want to modify a Gutenberg block..."
```
src/blocks/[block-name]/
├─ block.json          ← Block name, category, icon
├─ index.js            ← Register the block
├─ edit.js             ← What editor users see
├─ save.js             ← What gets saved to post content
├─ view.js             ← What frontend users see
├─ style.scss          ← Frontend CSS
└─ editor.scss         ← Editor-only CSS
```

### "I want to add a reusable component..."
```
src/shared/components/
├─ BlockControls.js    ← Common block settings
├─ BlockSettings.js
├─ ErrorBoundary.js    ← Error handling
└─ index.js            ← Export all components
```

### "I want to use the API or caching..."
```
src/shared/services/     ← Available services
  ├─ API client
  ├─ Cache service
  └─ Utilities
```

### "I want to access the admin settings..."
```
includes/admin/
├─ class-settings.php   ← Admin UI & form handling
└─ settings-handler.php ← Settings save logic
```

### "I want to add a new PHP service..."
```
includes/classes/
├─ Create class-blockxpert-[service].php
├─ Implement ServiceInterface
└─ Register in class-plugin.php
```

### "I see a bug - where should I check?"
```
1. For UI issues       → src/blocks/[block]/edit.js
2. For frontend issues → src/blocks/[block]/view.js
3. For rendering       → src/blocks/[block]/save.js
4. For admin issues    → includes/admin/
5. For API errors      → includes/classes/class-blockxpert-rest.php
6. For logging         → includes/classes/class-blockxpert-logger.php
```

---

## 🎯 File Location Quick Lookup

| Task | Location | File(s) |
|------|----------|---------|
| Add new block | `src/blocks/new-block/` | block.json, index.js, edit.js, save.js, view.js |
| Modify block UI | `src/blocks/[block]/edit.js` | React component |
| Style frontend | `src/blocks/[block]/style.scss` | Frontend CSS |
| Add API endpoint | `includes/classes/class-blockxpert-rest.php` | REST API |
| Change admin UI | `includes/admin/class-settings.php` | Admin page |
| Add logging | `includes/classes/class-blockxpert-logger.php` | Logger service |
| Shared component | `src/shared/components/` | React component |
| Custom hook | `src/shared/hooks/` | useAPI, useCache, etc |
| Global state | `src/shared/context/` | React Context |
| Build config | `webpack.config.js` | Webpack |
| npm scripts | `package.json` | scripts section |
| Plugin init | `blockxpert.php` | Main entry |

---

## 🚀 Common Development Tasks

### Creating a New Block

```bash
# 1. Create block folder structure
mkdir -p src/blocks/my-block

# 2. Create required files in order:
src/blocks/my-block/
├─ block.json           ← Copy from existing block, change name
├─ index.js             ← Import & register block
├─ edit.js              ← Your React editor component
├─ view.js              ← Frontend JavaScript
├─ save.js              ← Static markup output
├─ style.scss           ← Frontend styles
├─ editor.scss          ← Editor-only styles
└─ README.md            ← Documentation

# 3. Build
npm run build

# 4. Activate in WordPress admin settings
```

### Reusing a Component

```javascript
// Instead of creating new component, import from shared
import { BlockControls, BlockSettings, ErrorBoundary } from '../../../shared/components';

// All exported from centralized location
```

### Adding Logging

```javascript
// In PHP backend
$logger = $container->get( 'logger' );
$logger->log( 'My message', 'info' );
$logger->log( 'Error occurred', 'error' );
```

### Using Cache

```javascript
// In React component
import { useCache } from '../../../shared/hooks';

const { get, set } = useCache();
const data = get('key') || await fetchData();
set('key', data, 3600); // 1 hour
```

---

## 📊 Folder Size Guide

```
src/              ~125 KB (source code - version control)
includes/         ~85 KB (PHP backend - version control)
build/            ~380 KB (COMPILED - can be .gitignored)
languages/        ~5 KB (translations)
vendor/           ~15 MB (dependencies - .gitignore)
node_modules/     ~450 MB (dependencies - .gitignore)
```

**Deployed = ~610 KB** (Just src/ + includes/ + compiled blocks + vendor/)

---

## ✅ Maintenance Checklist

### When Adding a New Block
- [ ] Create folder in `src/blocks/new-block/`
- [ ] Copy structure from similar block
- [ ] Update `block.json` with unique name & category
- [ ] Implement `index.js` with registerBlockType()
- [ ] Create editor component in `edit.js`
- [ ] Create frontend code in `view.js` and `style.scss`
- [ ] Run `npm run build`
- [ ] Test in WordPress editor
- [ ] Create `README.md` documenting the block

### When Modifying Styles
- [ ] For frontend: modify `src/blocks/[block]/style.scss`
- [ ] For editor: modify `src/blocks/[block]/editor.scss`
- [ ] Run `npm run build`
- [ ] Test in browser (frontend) and editor

### Before Deployment
- [ ] Run `npm run build:prod`
- [ ] Run `npm run lint` (code quality)
- [ ] Run `npm run format` (code formatting)
- [ ] Test all blocks in WordPress
- [ ] Check admin settings page
- [ ] Verify REST API endpoints

---

## 🔗 Key File Dependencies

```
blockxpert.php (entry)
    ↓
includes/init.php (initialize)
    ↓
includes/interfaces/* (define contracts)
    ↓
includes/classes/* (implement contracts)
    ↓
includes/admin/class-settings.php (admin UI)
    ↓
src/blocks/*/block.json (block configs)
```

Frontend:
```
src/index.js (entry)
    ↓
src/blocks/*/index.js (register blocks)
    ↓
src/blocks/*/edit.js (editor)
    ↓
src/shared/* (reusable components)
```

---

## 📞 Architecture Decision Guide

### "Should this code go in..."

**`src/blocks/[block]/`** if:
- ✅ Block-specific UI
- ✅ Block-specific styling
- ✅ Block-specific attributes
- ✅ Block-specific logic

**`src/shared/components/`** if:
- ✅ Used by 2+ blocks
- ✅ Generic/reusable
- ✅ No block-specific logic
- ✅ Can work standalone

**`src/shared/hooks/`** if:
- ✅ Reusable React logic
- ✅ Multiple blocks use it
- ✅ Encapsulates state/effects

**`src/shared/services/`** if:
- ✅ API communication
- ✅ Data fetching/processing
- ✅ External service integration

**`includes/classes/`** if:
- ✅ PHP backend logic
- ✅ Service orchestration
- ✅ Database operations
- ✅ WordPress integration

**`includes/interfaces/`** if:
- ✅ Defining service contract
- ✅ Multiple implementations possible
- ✅ Need loose coupling

---

## 🎓 Learning Path

1. **Start here**: Read [BLOCK_DEVELOPMENT_GUIDE.md](./BLOCK_DEVELOPMENT_GUIDE.md)
2. **Study existing**: Look at `src/blocks/text-animation/` (newest, best documented)
3. **Try simple**: Modify existing block styles (src/blocks/*/style.scss)
4. **Create block**: Use template to create simple block
5. **Advanced**: Study post-grid block's complex structure

---

## 📝 Quick Reference: npm Scripts

```bash
npm run build          # Development build
npm run build:prod     # Production build (optimized)
npm start              # Watch mode with auto-rebuild
npm run clean          # Clear build folder
npm run lint           # Check code quality
npm run format         # Auto-format code
npm run validate       # Validate all checks
```

```bash
composer install       # Install PHP dependencies
composer install --no-dev # Production (exclude dev)
```

---

**Last Updated**: Post-Production Review  
**Plugin Version**: 1.1.0  
**Status**: ✅ Production Ready

