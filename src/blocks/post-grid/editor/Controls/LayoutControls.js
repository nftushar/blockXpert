/**
 * Layout Controls Component
 * Handles all layout-related settings and controls
 */

import { PanelBody, SelectControl, RangeControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { LAYOUT_OPTIONS } from '../../settings/attributes';
import { CSS_CLASSES } from '../../settings/constants';

export default function LayoutControls({ attributes, setAttributes }) {
    const { layout, columns, postsToShow } = attributes;

    const handleLayoutChange = (newLayout) => {
        setAttributes({ layout: newLayout });
    };

    const handleColumnsChange = (newColumns) => {
        setAttributes({ columns: newColumns });
    };

    const handlePostsToShowChange = (newPostsToShow) => {
        setAttributes({ postsToShow: newPostsToShow });
    };

    const isColumnsSupported = layout === 'grid' || layout === 'masonry';

    return (
        <PanelBody
            title={__('Layout Settings', 'blockxpert')}
            initialOpen={true}
            className="blockxpert-apb-layout-controls"
        >
            <SelectControl
                label={__('Layout Type', 'blockxpert')}
                value={layout}
                options={LAYOUT_OPTIONS}
                onChange={handleLayoutChange}
                help={__('Choose how posts will be displayed', 'blockxpert')}
            />

            {isColumnsSupported && (
                <RangeControl
                    label={__('Columns', 'blockxpert')}
                    value={columns}
                    onChange={handleColumnsChange}
                    min={1}
                    max={6}
                    help={__('Number of columns in the grid layout', 'blockxpert')}
                />
            )}

            <RangeControl
                label={__('Posts to Show', 'blockxpert')}
                value={postsToShow}
                onChange={handlePostsToShowChange}
                min={1}
                max={20}
                help={__('Maximum number of posts to display', 'blockxpert')}
            />

            <div className="blockxpert-apb-layout-preview">
                <p className="blockxpert-apb-preview-label">
                    {__('Layout Preview:', 'blockxpert')} {layout}
                </p>
                {isColumnsSupported && (
                    <p className="blockxpert-apb-preview-details">
                        {__('Columns:', 'blockxpert')} {columns} | {__('Posts:', 'blockxpert')} {postsToShow}
                    </p>
                )}
            </div>
        </PanelBody>
    );
}
