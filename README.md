# BlockXpert

A powerful set of AI-driven Gutenberg blocks for WordPress, including AI FAQ and Product Recommendations for WooCommerce.

## Features

- **AI-Powered FAQ Block**: Automatically generate relevant questions and answers
- **AI Product Recommendations**: Smart product suggestions for WooCommerce
- **Product Slider Block**: Elegant product showcase
- **Advanced Post Block**: Enhanced post display capabilities
- **Centralized Admin Panel**: Enable/disable blocks from one place

## Development Setup

### Prerequisites

- Node.js (v14 or higher)
- npm or yarn
- WordPress development environment

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/nftushar/blockxpert.git
   cd blockxpert
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Build the plugin:
   ```bash
   npm run build
   ```

### Development Commands

- `npm run build` - Build production assets
- `npm run dev` - Start development mode with watch
- `npm run lint` - Run ESLint
- `npm run format` - Format code with Prettier

### Project Structure

```
BlockXpert/
├── src/                    # Source code
│   ├── blocks/            # Individual block components
│   │   ├── advanced-post-block/
│   │   ├── ai-faq/
│   │   ├── ai-product-recommendations/

│   │   └── product-slider/
│   └── index.js           # Main entry point
├── includes/              # PHP backend files
│   ├── admin/            # Admin interface
│   ├── assets/           # Compiled assets
│   └── classes/          # PHP classes
├── blocks/               # Block registration files
└── webpack.config.js     # Build configuration
```

### Building for Production

1. Ensure all dependencies are installed:
   ```bash
   npm install
   ```

2. Build the production assets:
   ```bash
   npm run build
   ```

3. The compiled assets will be generated in the appropriate directories for WordPress to use.

### Block Development

Each block follows a consistent structure:

- `edit.js` - Editor component (React)
- `view.js` - Frontend component (React)
- `index.js` - Block registration
- `style.scss` - Block styles

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and build
5. Submit a pull request

## License

GPL v2 or later

## Support

For support and questions, please visit the [WordPress.org plugin page](https://wordpress.org/plugins/blockxpert/) or create an issue on GitHub.
