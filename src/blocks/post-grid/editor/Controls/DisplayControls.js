/**
 * Display Controls Component
 * Handles all display-related settings and controls
 */

import { 
    PanelBody, 
    ToggleControl, 
    RangeControl, 
    SelectControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { IMAGE_SIZE_OPTIONS } from '../../settings/attributes';

export default function DisplayControls({ attributes, setAttributes }) {
    const {
        showExcerpt,
        showDate,
        showAuthor,
        showImage,
        showReadMore,
        excerptLength,
        imageSize
    } = attributes;

    const handleToggleChange = (key, value) => {
        setAttributes({ [key]: value });
    };

    const handleExcerptLengthChange = (newLength) => {
        setAttributes({ excerptLength: newLength });
    };

    const handleImageSizeChange = (newSize) => {
        setAttributes({ imageSize: newSize });
    };

    return (
        <PanelBody 
            title={__('Display Settings', 'blockxpert')} 
            initialOpen={false}
            className="blockxpert-apb-display-controls"
        >
            <div className="blockxpert-apb-display-section">
                <h4 className="blockxpert-apb-section-title">
                    {__('Content Display', 'blockxpert')}
                </h4>
                
                <ToggleControl
                    label={__('Show Featured Image', 'blockxpert')}
                    checked={showImage}
                    onChange={(value) => handleToggleChange('showImage', value)}
                    help={__('Display the featured image for each post', 'blockxpert')}
                />

                <ToggleControl
                    label={__('Show Excerpt', 'blockxpert')}
                    checked={showExcerpt}
                    onChange={(value) => handleToggleChange('showExcerpt', value)}
                    help={__('Display post excerpt or content preview', 'blockxpert')}
                />

                <ToggleControl
                    label={__('Show Date', 'blockxpert')}
                    checked={showDate}
                    onChange={(value) => handleToggleChange('showDate', value)}
                    help={__('Display the publication date', 'blockxpert')}
                />

                <ToggleControl
                    label={__('Show Author', 'blockxpert')}
                    checked={showAuthor}
                    onChange={(value) => handleToggleChange('showAuthor', value)}
                    help={__('Display the post author name', 'blockxpert')}
                />

                <ToggleControl
                    label={__('Show Read More Link', 'blockxpert')}
                    checked={showReadMore}
                    onChange={(value) => handleToggleChange('showReadMore', value)}
                    help={__('Display a read more link for each post', 'blockxpert')}
                />
            </div>

            {showExcerpt && (
                <div className="blockxpert-apb-excerpt-settings">
                    <h4 className="blockxpert-apb-section-title">
                        {__('Excerpt Settings', 'blockxpert')}
                    </h4>
                    
                    <RangeControl
                        label={__('Excerpt Length', 'blockxpert')}
                        value={excerptLength}
                        onChange={handleExcerptLengthChange}
                        min={50}
                        max={500}
                        step={10}
                        help={__('Number of characters in the excerpt', 'blockxpert')}
                    />
                </div>
            )}

            {showImage && (
                <div className="blockxpert-apb-image-settings">
                    <h4 className="blockxpert-apb-section-title">
                        {__('Image Settings', 'blockxpert')}
                    </h4>
                    
                    <SelectControl
                        label={__('Image Size', 'blockxpert')}
                        value={imageSize}
                        options={IMAGE_SIZE_OPTIONS}
                        onChange={handleImageSizeChange}
                        help={__('Size of the featured image', 'blockxpert')}
                    />
                </div>
            )}

            <div className="blockxpert-apb-display-preview">
                <p className="blockxpert-apb-preview-label">
                    {__('Display Preview:', 'blockxpert')}
                </p>
                <div className="blockxpert-apb-preview-items">
                    {showImage && <span className="blockxpert-apb-preview-item">📷 Image</span>}
                    {showExcerpt && <span className="blockxpert-apb-preview-item">📝 Excerpt</span>}
                    {showDate && <span className="blockxpert-apb-preview-item">📅 Date</span>}
                    {showAuthor && <span className="blockxpert-apb-preview-item">👤 Author</span>}
                    {showReadMore && <span className="blockxpert-apb-preview-item">🔗 Read More</span>}
                </div>
            </div>
        </PanelBody>
    );
}
