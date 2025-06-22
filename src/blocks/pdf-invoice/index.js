import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('blockxpert/pdf-invoice', {
  edit: Edit,
  save: () => null, // dynamic block
}); 