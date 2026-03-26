/**
 * Block Improvements Utilities & Components
 * Central export for all block enhancement tools
 */

// Hooks
export { useBlockAttributes } from '../hooks/useBlockAttributes';

// Components
export { BlockWrapper } from './BlockWrapper';
export {
    LayoutSettings,
    PaginationSettings,
    DisplaySettings,
    QuerySettings,
    StyleSettings,
} from './BlockSettings';

// Utilities
export { BlockPresets, getPreset, listPresets } from '../utils/BlockPresets';
export { blockRegister, batchRegisterBlocks, createBlockConfig } from '../utils/blockRegister';

// Summary
console.info('✓ Block Improvements loaded successfully');
