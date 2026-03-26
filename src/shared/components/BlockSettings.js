/**
 * BlockSettings Component
 * Provides common block settings controls
 */

import {
    PanelBody,
    SelectControl,
    RangeControl,
    ToggleControl,
    TextControl,
    __experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Layout Settings Panel
 */
export function LayoutSettings({ attributes, updateAttribute }) {
    return (
        <PanelBody title={__('Layout Settings', 'blockxpert')}>
            <SelectControl
                label={__('Layout Type', 'blockxpert')}
                value={attributes.layout || 'grid'}
                options={[
                    { label: __('Grid', 'blockxpert'), value: 'grid' },
                    { label: __('List', 'blockxpert'), value: 'list' },
                    { label: __('Masonry', 'blockxpert'), value: 'masonry' },
                    { label: __('Slider', 'blockxpert'), value: 'slider' },
                ]}
                onChange={(value) => updateAttribute('layout', value)}
            />

            {['grid', 'masonry'].includes(attributes.layout) && (
                <RangeControl
                    label={__('Columns', 'blockxpert')}
                    value={attributes.columns || 3}
                    onChange={(value) => updateAttribute('columns', value)}
                    min={1}
                    max={6}
                />
            )}

            <RangeControl
                label={__('Gap', 'blockxpert')}
                value={attributes.gap || 16}
                onChange={(value) => updateAttribute('gap', value)}
                min={0}
                max={60}
                step={4}
            />
        </PanelBody>
    );
}

/**
 * Pagination Settings Panel
 */
export function PaginationSettings({ attributes, updateAttribute }) {
    return (
        <PanelBody title={__('Pagination', 'blockxpert')}>
            <RangeControl
                label={__('Items Per Page', 'blockxpert')}
                value={attributes.itemsPerPage || 12}
                onChange={(value) => updateAttribute('itemsPerPage', value)}
                min={1}
                max={100}
                step={1}
            />

            <ToggleControl
                label={__('Show Pagination', 'blockxpert')}
                checked={attributes.showPagination !== false}
                onChange={(value) => updateAttribute('showPagination', value)}
            />

            <ToggleControl
                label={__('Load More Button', 'blockxpert')}
                checked={attributes.loadMoreButton}
                onChange={(value) => updateAttribute('loadMoreButton', value)}
            />
        </PanelBody>
    );
}

/**
 * Display Settings Panel
 */
export function DisplaySettings({ attributes, updateAttribute }) {
    return (
        <PanelBody title={__('Display Settings', 'blockxpert')}>
            <ToggleControl
                label={__('Show Featured Image', 'blockxpert')}
                checked={attributes.showImage !== false}
                onChange={(value) => updateAttribute('showImage', value)}
            />

            <ToggleControl
                label={__('Show Title', 'blockxpert')}
                checked={attributes.showTitle !== false}
                onChange={(value) => updateAttribute('showTitle', value)}
            />

            <ToggleControl
                label={__('Show Excerpt', 'blockxpert')}
                checked={attributes.showExcerpt}
                onChange={(value) => updateAttribute('showExcerpt', value)}
            />

            <ToggleControl
                label={__('Show Meta', 'blockxpert')}
                checked={attributes.showMeta}
                onChange={(value) => updateAttribute('showMeta', value)}
            />

            <ToggleControl
                label={__('Show Date', 'blockxpert')}
                checked={attributes.showDate}
                onChange={(value) => updateAttribute('showDate', value)}
            />

            <ToggleControl
                label={__('Show Author', 'blockxpert')}
                checked={attributes.showAuthor}
                onChange={(value) => updateAttribute('showAuthor', value)}
            />

            <ToggleControl
                label={__('Show Category', 'blockxpert')}
                checked={attributes.showCategory}
                onChange={(value) => updateAttribute('showCategory', value)}
            />
        </PanelBody>
    );
}

/**
 * Query Settings Panel
 */
export function QuerySettings({ attributes, updateAttribute }) {
    return (
        <PanelBody title={__('Query Settings', 'blockxpert')}>
            <SelectControl
                label={__('Post Type', 'blockxpert')}
                value={attributes.postType || 'post'}
                options={[
                    { label: __('Posts', 'blockxpert'), value: 'post' },
                    { label: __('Pages', 'blockxpert'), value: 'page' },
                ]}
                onChange={(value) => updateAttribute('postType', value)}
            />

            <SelectControl
                label={__('Order By', 'blockxpert')}
                value={attributes.orderBy || 'date'}
                options={[
                    { label: __('Date', 'blockxpert'), value: 'date' },
                    { label: __('Title', 'blockxpert'), value: 'title' },
                    { label: __('Random', 'blockxpert'), value: 'rand' },
                    { label: __('Modified', 'blockxpert'), value: 'modified' },
                ]}
                onChange={(value) => updateAttribute('orderBy', value)}
            />

            <SelectControl
                label={__('Order', 'blockxpert')}
                value={attributes.order || 'desc'}
                options={[
                    { label: __('Descending', 'blockxpert'), value: 'desc' },
                    { label: __('Ascending', 'blockxpert'), value: 'asc' },
                ]}
                onChange={(value) => updateAttribute('order', value)}
            />
        </PanelBody>
    );
}

/**
 * Style Settings Panel
 */
export function StyleSettings({ attributes, updateAttribute }) {
    return (
        <PanelBody title={__('Styling', 'blockxpert')}>
            <SelectControl
                label={__('Card Style', 'blockxpert')}
                value={attributes.cardStyle || 'default'}
                options={[
                    { label: __('Default', 'blockxpert'), value: 'default' },
                    { label: __('Minimal', 'blockxpert'), value: 'minimal' },
                    { label: __('Elevated', 'blockxpert'), value: 'elevated' },
                    { label: __('Outline', 'blockxpert'), value: 'outline' },
                ]}
                onChange={(value) => updateAttribute('cardStyle', value)}
            />

            <RangeControl
                label={__('Border Radius', 'blockxpert')}
                value={attributes.borderRadius || 0}
                onChange={(value) => updateAttribute('borderRadius', value)}
                min={0}
                max={50}
                step={1}
            />

            <RangeControl
                label={__('Shadow', 'blockxpert')}
                value={attributes.shadow || 0}
                onChange={(value) => updateAttribute('shadow', value)}
                min={0}
                max={20}
                step={1}
            />
        </PanelBody>
    );
}

export default {
    LayoutSettings,
    PaginationSettings,
    DisplaySettings,
    QuerySettings,
    StyleSettings,
};
