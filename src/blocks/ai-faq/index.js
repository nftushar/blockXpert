/**
 * BlockXpert AI FAQ Block
 * Main entry point for block registration
 */

import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

/**
 * Register the block
 */
registerBlockType(metadata.name, {
    // Use metadata from block.json
    ...metadata,
    
    // Editor component
    edit: Edit,
    
    // Dynamic block - no static save
    save: () => null
}); 