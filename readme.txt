=== BlockXpert ===
Contributors: nftushar
Tags: blocks, gutenberg, ai, openai, woocommerce
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful set of AI-driven Gutenberg blocks, including an AI FAQ and Ai Product Recommendations for WooCommerce, with comprehensive admin controls.

== Description ==

BlockXpert is your AI-powered content assistant for the WordPress block editor. Supercharge your website with intelligent, dynamic blocks that enhance user experience and drive engagement. This plugin provides a suite of blocks, including an AI-powered FAQ generator and a smart product recommendation engine for WooCommerce.

= Features =

*   **AI-Powered FAQ Block**: Automatically generate relevant questions and answers for your pages. Perfect for product descriptions, support pages, and more. Connect your OpenAI account and let the AI do the work.
*   **Ai Product Recommendations for WooCommerce**: A smart block that suggests products to users based on context. Increase your sales with intelligent upsells, cross-sells, and related product suggestions.
*   **Product Slider Block**: A simple, elegant slider to showcase your WooCommerce products.
*   **Simple Static Blocks**: Includes several basic blocks for demonstration and boilerplate.
*   **Centralized Admin Panel**: Enable or disable any block from a single, easy-to-use settings page to keep your editor clean.
*   **Developer Friendly**: Built with a modular architecture that is easy to extend.

== Source Code ==

The full source code for this plugin is available on [GitHub](https://github.com/nftushar/blockxpert). The distributed plugin includes only the production-ready, compiled JavaScript and CSS in the `build/` directory.

== Build Instructions ==

To review or contribute to the source code, or to build the plugin yourself:

1. Clone the repository from GitHub.
2. Run `npm install` to install dependencies.
3. Run `npm run build` to generate the production assets.

For detailed development instructions, see the README.md file in the plugin directory.

== External services ==

This plugin connects to OpenAI's API ([https://api.openai.com/v1/chat/completions](https://api.openai.com/v1/chat/completions)) to provide AI-powered features for generating FAQ content and product recommendations. When you use the AI blocks, your page content (text, product descriptions) and your OpenAI API key are sent to OpenAI's API to generate relevant content. No data is sent automatically—only when you actively use the AI blocks in the editor.

This service is provided by OpenAI. Please review their [terms of use](https://openai.com/policies/terms-of-use) and [privacy policy](https://openai.com/policies/privacy-policy).

== Installation ==

1.  Upload the `blockxpert` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to the 'BlockXpert' admin menu.
4.  Enable the blocks you want to use.
5.  For AI features, enter your OpenAI API key in the block settings in the editor sidebar.

== Frequently Asked Questions ==

= Do I need an OpenAI API key? =

Yes, for the AI-powered features (AI FAQ and Ai Product Recommendations), you will need to provide your own OpenAI API key in the block's settings panel in the editor.

= Is WooCommerce required? =

WooCommerce is only required for the blocks that interact with products, such as the Product Slider and Ai Product Recommendations. Other blocks will work without it.

= Can I customize the look and feel? =

Yes! Most blocks come with built-in styling options, such as light/dark themes and different layouts (grid, list, slider).

= What data is sent to OpenAI? =

When you use the AI features, your page content (text, product descriptions) is sent to OpenAI's API to generate relevant FAQs or product recommendations. Your API key is also sent for authentication. No data is sent automatically - only when you actively use the AI blocks.

= Is my data secure? =

Your data is sent to OpenAI's secure API endpoints. We do not store your API key or the content you send to OpenAI. Please review OpenAI's privacy policy for more information about how they handle your data.

== Screenshots ==

1.  The BlockXpert admin panel for enabling/disabling blocks.
2.  The AI FAQ block in the editor with generated questions.
3.  The Ai Product Recommendations block showing related items on a product page.
4.  The product slider in action.

== Changelog ==

= 1.0.0 =
*   Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release of BlockXpert. 