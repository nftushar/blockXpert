/**
 * Post Ticker Component
 * Horizontal scrolling ticker layout for displaying posts
 */

import { CSS_CLASSES } from '../../settings/constants';
import PostCard from './PostCard';

export default function PostTicker({ 
    attributes, 
    posts, 
    containerProps 
}) {
    const { columns = 1 } = attributes;

    const tickerClasses = [
        CSS_CLASSES.blockContainer,
        CSS_CLASSES.tickerLayout,
        'blockxpert-apb-ticker-wrapper'
    ].filter(Boolean).join(' ');

    if (!posts || posts.length === 0) {
        return (
            <div className={tickerClasses}>
                <div className="blockxpert-apb-empty-state">
                    <p>{__('No posts found matching your criteria.', 'blockxpert')}</p>
                </div>
            </div>
        );
    }

    return (
        <div 
            className={tickerClasses}
            style={containerProps.style}
            {...containerProps}
        >
            <div className="blockxpert-apb-ticker-container">
                <div className="blockxpert-apb-ticker-content">
                    {posts.map((post, index) => (
                        <div 
                            key={post.id}
                            className={CSS_CLASSES.postItem}
                            style={{ 
                                width: `${100 / columns}%`,
                                animationDelay: `${index * 100}ms`
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
            </div>
        </div>
    );
}
