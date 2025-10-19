/**
 * Advanced Post Block Frontend View
 * Server-side rendering component for the frontend
 */

import { CSS_CLASSES } from '../settings/constants';

export default function AdvancedPostBlockView({ attributes, posts }) {
    const {
        layout,
        columns,
        showExcerpt,
        showDate,
        showAuthor,
        showImage,
        showReadMore,
        excerptLength,
        imageSize,
        cardSpacing,
        borderRadius,
        showShadows
    } = attributes;

    const blockClasses = [
        CSS_CLASSES.blockWrapper,
        CSS_CLASSES.blockLayout,
        `${CSS_CLASSES.blockLayout}--${layout}`,
        `blockxpert-apb-columns-${columns}`
    ].filter(Boolean).join(' ');

    const containerClasses = [
        CSS_CLASSES.blockContainer,
        layout === 'grid' ? CSS_CLASSES.gridLayout : '',
        layout === 'masonry' ? CSS_CLASSES.masonryLayout : '',
        layout === 'slider' ? CSS_CLASSES.sliderLayout : '',
        layout === 'ticker' ? CSS_CLASSES.tickerLayout : ''
    ].filter(Boolean).join(' ');

    const containerStyle = {
        '--blockxpert-apb-card-spacing': `${cardSpacing}px`,
        '--blockxpert-apb-border-radius': `${borderRadius}px`,
        '--blockxpert-apb-shadow': showShadows ? '0 4px 6px rgba(0, 0, 0, 0.1)' : 'none'
    };

    if (!posts || posts.length === 0) {
        return (
            <div className={blockClasses}>
                <div className={CSS_CLASSES.editorPlaceholder}>
                    <p>{__('No posts found matching your criteria.', 'blockxpert')}</p>
                </div>
            </div>
        );
    }

    return (
        <div className={blockClasses}>
            <div className={containerClasses} style={containerStyle}>
                {posts.map((post, index) => (
                    <div 
                        key={post.id} 
                        className={CSS_CLASSES.postItem}
                        style={{ animationDelay: `${index * 100}ms` }}
                    >
                        <article className={CSS_CLASSES.postCard}>
                            {showImage && post.featured_media && (
                                <div className={CSS_CLASSES.postImage}>
                                    <img 
                                        src={post._embedded?.['wp:featuredmedia']?.[0]?.source_url || ''}
                                        alt={post.title.rendered}
                                        loading="lazy"
                                    />
                                </div>
                            )}
                            
                            <div className={CSS_CLASSES.postContent}>
                                <h3 className={CSS_CLASSES.postTitle}>
                                    <a href={post.link} dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
                                </h3>
                                
                                {showExcerpt && post.excerpt && (
                                    <div className={CSS_CLASSES.postExcerpt}>
                                        <p dangerouslySetInnerHTML={{ 
                                            __html: post.excerpt.rendered.substring(0, excerptLength) + '...' 
                                        }} />
                                    </div>
                                )}
                                
                                <div className={CSS_CLASSES.postMeta}>
                                    {showDate && (
                                        <time className={CSS_CLASSES.postDate} dateTime={post.date}>
                                            {new Date(post.date).toLocaleDateString()}
                                        </time>
                                    )}
                                    
                                    {showAuthor && post._embedded?.author?.[0] && (
                                        <span className={CSS_CLASSES.postAuthor}>
                                            {__('By', 'blockxpert')} {post._embedded.author[0].name}
                                        </span>
                                    )}
                                </div>
                                
                                {showReadMore && (
                                    <a 
                                        href={post.link} 
                                        className={CSS_CLASSES.readMoreLink}
                                    >
                                        {__('Read More', 'blockxpert')}
                                    </a>
                                )}
                            </div>
                        </article>
                    </div>
                ))}
            </div>
        </div>
    );
}
