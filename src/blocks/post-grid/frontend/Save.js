/**
 * Advanced Post Block Save Component
 * Handles the frontend rendering of the block
 */

import { useBlockProps } from '@wordpress/block-editor';
import PostGrid from './components/PostGrid';
import PostSlider from './components/PostSlider';
import PostTicker from './components/PostTicker';
import { CSS_CLASSES } from '../settings/constants';

export default function AdvancedPostBlockSave({ attributes }) {
    const {
        layout,
        columns,
        postsToShow,
        showExcerpt,
        showDate,
        showAuthor,
        showImage,
        showReadMore,
        excerptLength,
        imageSize,
        postType,
        categories,
        tags,
        orderBy,
        order,
        cardSpacing,
        borderRadius,
        showShadows
    } = attributes;

    const blockProps = useBlockProps({
        className: [
            CSS_CLASSES.blockWrapper,
            CSS_CLASSES.blockLayout,
            `${CSS_CLASSES.blockLayout}--${layout}`,
            `blockxpert-apb-columns-${columns}`
        ].filter(Boolean).join(' ')
    });

    const containerProps = {
        className: CSS_CLASSES.blockContainer,
        style: {
            '--blockxpert-apb-card-spacing': `${cardSpacing}px`,
            '--blockxpert-apb-border-radius': `${borderRadius}px`,
            '--blockxpert-apb-shadow': showShadows ? '0 4px 6px rgba(0, 0, 0, 0.1)' : 'none'
        }
    };

    const renderLayout = () => {
        const commonProps = {
            attributes,
            containerProps
        };

        switch (layout) {
            case 'grid':
                return <PostGrid {...commonProps} />;
            case 'masonry':
                return <PostGrid {...commonProps} isMasonry={true} />;
            case 'slider':
                return <PostSlider {...commonProps} />;
            case 'ticker':
                return <PostTicker {...commonProps} />;
            default:
                return <PostGrid {...commonProps} />;
        }
    };

    return (
        <div {...blockProps}>
            {renderLayout()}
        </div>
    );
}
