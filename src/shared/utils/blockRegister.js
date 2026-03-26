/**
 * blockRegister Utility
 * Enhanced block registration with validation and defaults
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Enhanced block registration with error handling and defaults
 */
export function blockRegister(name, config) {
    try {
        // Validate block name format
        if (!name || typeof name !== 'string') {
            throw new Error('Block name must be a non-empty string');
        }

        if (!name.includes('/')) {
            throw new Error(`Block name must follow format "namespace/blockName", got "${name}"`);
        }

        // Set defaults for common config properties
        const enhancedConfig = {
            // Required by WordPress
            title: config.title || __(name.split('/')[1], 'blockxpert'),
            
            // Optional but recommended
            description: config.description || '',
            category: config.category || 'blockxpert',
            icon: config.icon || 'block-default',
            keywords: config.keywords || [],
            
            // Functionality
            ...config,
        };

        // Validate edit function exists
        if (typeof enhancedConfig.edit !== 'function') {
            throw new Error(`Block ${name} must have an edit function`);
        }

        // Log registration in development
        if (process.env.NODE_ENV === 'development') {
            console.info(`✓ Registering block: ${name}`);
        }

        // Register the block
        const result = registerBlockType(name, enhancedConfig);

        return result;
    } catch (error) {
        console.error(`Error registering block ${name}:`, error);
        throw error;
    }
}

/**
 * Batch register multiple blocks
 */
export function batchRegisterBlocks(blocks) {
    const registered = [];
    const failed = [];

    blocks.forEach(({ name, config }) => {
        try {
            blockRegister(name, config);
            registered.push(name);
        } catch (error) {
            failed.push({ name, error: error.message });
        }
    });

    if (process.env.NODE_ENV === 'development') {
        console.info(`Registered: ${registered.length} blocks`);
        if (failed.length > 0) {
            console.warn(`Failed: ${failed.length} blocks`, failed);
        }
    }

    return { registered, failed };
}

/**
 * Create block config template with defaults
 */
export function createBlockConfig(overrides = {}) {
    return {
        // Display
        title: '',
        description: '',
        category: 'blockxpert',
        icon: 'block-default',
        keywords: [],

        // Metadata
        attributes: {},
        supports: {
            html: false,
            className: true,
            color: {
                background: true,
                text: true,
            },
            spacing: {
                padding: true,
                margin: true,
            },
        },

        // Functions
        edit: () => null,
        save: () => null,

        // Merge overrides
        ...overrides,
    };
}

export default {
    blockRegister,
    batchRegisterBlocks,
    createBlockConfig,
};
