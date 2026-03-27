import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import './style.scss';

registerBlockType(metadata.name, {
  ...metadata,
  icon: '💡',
  edit: Edit,
  save: () => null, // Dynamic block, rendered server-side
}); 