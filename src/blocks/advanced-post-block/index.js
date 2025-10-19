import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './editor/Edit';

registerBlockType('blockxpert/advanced-post-block', {
    edit: Edit,
    save: () => null, // Dynamic block, rendered server-side
});