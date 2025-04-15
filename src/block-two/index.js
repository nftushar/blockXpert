import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import 'style-loader!./style.css';

 
registerBlockType('gutenberg-blocks/block-three', {
    edit: Edit,
    save: () => null,
    icon: 'star-filled'
});