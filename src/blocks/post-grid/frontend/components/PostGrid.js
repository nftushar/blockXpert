/**
 * Post Grid Component
 * Grid layout for displaying posts
 */

import { CSS_CLASSES } from '../../settings/constants';
import PostCard from './PostCard';

export default function PostGrid({ 
    attributes, 
    posts, 
    containerProps,
    isMasonry = false 
}) {
    const { columns, layout } = attributes;

    const gridClasses = [
        CSS_CLASSES.blockContainer,
        isMasonry ? CSS_CLASSES.masonryLayout : CSS_CLASSES.gridLayout,
        `blockxpert-apb-grid-columns-${columns}`,
        isMasonry ? 'blockxpert-apb-masonry-enabled' : ''
    ].filter(Boolean).join(' ');

    const gridStyle = {
        '--blockxpert-apb-grid-columns': columns,
        '--blockxpert-apb-grid-gap': `${attributes.cardSpacing || 20}px`
    };

    if (!posts || posts.length === 0) {
        return (
            <div className={gridClasses} style={gridStyle}>
                <div className="blockxpert-apb-empty-state">
                    <p>{__('No posts found matching your criteria.', 'blockxpert')}</p>
                </div>
            </div>
        );
    }

    return (
        <div 
            className={gridClasses} 
            style={{ ...gridStyle, ...containerProps.style }}
            {...containerProps}
        >
            {posts.map((post, index) => (
                <div 
                    key={post.id} 
                    className={CSS_CLASSES.postItem}
                    style={{ 
                        animationDelay: `${index * 100}ms`,
                        '--blockxpert-apb-item-index': index
                    }}
                >
                    <PostCard 
                        post={post} 
                        attributes={attributes} 
                        index={index}
                    />
                </div>
            ))}
        </div>
    );
}
