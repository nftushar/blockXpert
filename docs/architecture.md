# BlockXpert Plugin Architecture

## Overview

The BlockXpert plugin has been restructured for better scalability, maintainability, and developer experience. The new architecture follows modern WordPress and React patterns with clear separation of concerns.

## Directory Structure

```
src/
├── 📁 blocks/                          # Block definitions
│   ├── 📁 advanced-post-block/
│   │   ├── 📁 editor/                  # Editor-specific components
│   │   │   ├── Edit.js                 # Main editor component
│   │   │   ├── 📁 Controls/            # Control components
│   │   │   │   ├── LayoutControls.js
│   │   │   │   ├── DisplayControls.js
│   │   │   │   └── PostControls.js
│   │   │   ├── 📁 Preview/             # Preview components
│   │   │   │   ├── PostPreview.js
│   │   │   │   └── LayoutPreview.js
│   │   │   └── 📁 hooks/               # Custom hooks
│   │   │       ├── usePostLayout.js
│   │   │       └── usePostData.js
│   │   ├── 📁 frontend/               # Frontend-specific components
│   │   │   ├── Save.js                # Save component
│   │   │   ├── View.js                # Server-side rendering
│   │   │   └── 📁 components/         # Frontend components
│   │   │       ├── PostCard.js
│   │   │       ├── PostGrid.js
│   │   │       └── PostSlider.js
│   │   ├── 📁 settings/               # Settings and configuration
│   │   │   ├── block.json
│   │   │   ├── attributes.js
│   │   │   ├── constants.js
│   │   │   └── defaultSettings.js
│   │   ├── 📁 styles/                 # Block-specific styles
│   │   │   ├── editor.scss
│   │   │   ├── frontend.scss
│   │   │   └── components.scss
│   │   └── index.js                   # Main entry point
├── 📁 shared/                        # Shared components and utilities
│   ├── 📁 components/
│   │   ├── ErrorBoundary.js
│   │   ├── LoadingSpinner.js
│   │   └── Placeholder.js
│   ├── 📁 hooks/
│   │   ├── useAPI.js
│   │   ├── useCache.js
│   │   └── useDebounce.js
│   ├── 📁 services/
│   │   ├── WooCommerceService.js
│   │   ├── AIService.js
│   │   └── BlockXpertAPI.js
│   └── 📁 utils/
│       ├── constants.js
│       ├── helpers.js
│       └── validators.js
└── index.js                          # Main entry point
```

### PHP Runtime Structure

```
includes/
├── class-plugin.php
├── admin/
│   └── class-settings.php
└── classes/
	├── api/
	│   └── class-blockxpert-rest.php
	├── core/
	│   ├── class-blockxpert-service.php
	│   ├── class-blockxpert-cache.php
	│   └── class-blockxpert-blocks.php
	└── class-blockxpert-*.php        # Backward-compatible bridge files
```

Core runtime classes are grouped by domain (`core`, `api`) to reduce top-level clutter and make class discovery faster. Legacy include paths remain available through lightweight bridge files under `includes/classes/`.

## Key Improvements

> Build & Tooling

The build process now auto-discovers blocks under `src/blocks` and generates per-block assets (JS/CSS) in `build/<block-name>/` using `webpack.config.js`. Add `index.js`, `view.js`, `edit.js`, `editor.css` or `style.scss` files in your block folder and the build will pick them up automatically.

### Build Structure Files

- `src/blocks/index.js`: central block loader that auto-imports every `src/blocks/<block>/index.js`.
- `src/index.js`: single entry point that imports `src/blocks/index.js`.
- `scripts/build/getBlockEntries.js`: reusable entry discovery utility for webpack.
- `webpack.config.js`: focused on configuration only; entry discovery logic is delegated to `scripts/build/getBlockEntries.js`.

This keeps block onboarding simple: creating a new block folder with an `index.js` is enough for registration and build inclusion.

## Key Improvements

### 1. **Separation of Concerns**
- **Editor components** are isolated in their own folder
- **Frontend components** are separated for better organization
- **Settings** are centralized and easily maintainable
- **Styles** are organized by context (editor vs frontend)

### 2. **Professional CSS Classes**
- Renamed from generic classes like `apb-preview` to semantic classes like `blockxpert-apb-editor-preview`
- Consistent naming convention: `blockxpert-[block-name]-[element]-[modifier]`
- Better maintainability and debugging

### 3. **Modular Control Functions**
- **LayoutControls**: Handles layout-related settings
- **DisplayControls**: Manages display options
- **PostControls**: Controls post query parameters
- Each control is focused on a specific aspect

### 4. **Custom Hooks**
- **usePostLayout**: Manages layout logic and responsive behavior
- **usePostData**: Handles data fetching and state management
- Reusable logic that can be shared across components

### 5. **Shared Components**
- **ErrorBoundary**: Catches and handles JavaScript errors
- **LoadingSpinner**: Consistent loading indicators
- **Placeholder**: Reusable placeholder components

## Benefits

1. **Maintainability**: Clear structure makes it easy to find and modify code
2. **Scalability**: Easy to add new blocks following the same pattern
3. **Reusability**: Shared components and hooks reduce code duplication
4. **Testing**: Isolated components are easier to test
5. **Performance**: Optimized webpack configuration with code splitting
6. **Developer Experience**: Better IntelliSense and debugging

## Usage Examples

### Adding a New Block
1. Create block folder in `src/blocks/[block-name]/`
2. Follow the established structure (editor/, frontend/, settings/, styles/)
3. Implement required components
4. Update webpack configuration if needed

### Using Shared Components
```javascript
import ErrorBoundary from '@shared/components/ErrorBoundary';
import LoadingSpinner from '@shared/components/LoadingSpinner';
import { useAPI } from '@shared/hooks/useAPI';
```

### Custom Hooks
```javascript
import { usePostLayout } from './hooks/usePostLayout';
import { usePostData } from './hooks/usePostData';

const { getLayoutClasses, getResponsiveClasses } = usePostLayout(layout, columns);
const { posts, loading, error } = usePostData(queryParams);
```

## Migration Guide

To migrate existing blocks to the new structure:

1. **Move editor components** to `editor/` folder
2. **Move frontend components** to `frontend/` folder
3. **Create settings files** in `settings/` folder
4. **Update CSS classes** to use new naming convention
5. **Split large components** into smaller, focused components
6. **Extract reusable logic** into custom hooks
7. **Update import statements** to reflect new structure

This architecture provides a solid foundation for the BlockXpert plugin that can grow and evolve while maintaining code quality and performance.
