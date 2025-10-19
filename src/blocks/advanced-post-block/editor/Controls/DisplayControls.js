import { ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function DisplayControls({ attributes, setAttributes }) {
    return (
        <>
            <ToggleControl
                label={__('Show Featured Image', 'BlockXpert')}
                checked={attributes.showImage}
                onChange={(showImage) => setAttributes({ showImage })}
            />

            <ToggleControl
                label={__('Show Excerpt', 'BlockXpert')}
                checked={attributes.showExcerpt}
                onChange={(showExcerpt) => setAttributes({ showExcerpt })}
            />

            <ToggleControl
                label={__('Show Date', 'BlockXpert')}
                checked={attributes.showDate}
                onChange={(showDate) => setAttributes({ showDate })}
            />

            <ToggleControl
                label={__('Show Author', 'BlockXpert')}
                checked={attributes.showAuthor}
                onChange={(showAuthor) => setAttributes({ showAuthor })}
            />
        </>
    );
}