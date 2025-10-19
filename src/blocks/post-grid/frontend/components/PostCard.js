/**
 * Post Card Component
 * Individual post display component
 */

import { CSS_CLASSES } from '../../settings/constants';

export default function PostCard({ 
    post, 
    attributes, 
    index = 0 
}) {
    const {
        showExcerpt,
        showDate,
        showAuthor,
        showImage,
        showReadMore,
        excerptLength
    } = attributes;

    const {
        id,
        title,
        excerpt,
        link,
        date,
        featured_media,
        _embedded
    } = post;

    const featuredImage = _embedded?.['wp:featuredmedia']?.[0];
    const author = _embedded?.author?.[0];

    return (
        <article 
            className={CSS_CLASSES.postCard}
            style={{ 
                animationDelay: `${index * 100}ms`,
                '--blockxpert-apb-card-index': index
            }}
        >
            {showImage && featuredImage && (
                <div className={CSS_CLASSES.postImage}>
                    <img 
                        src={featuredImage.source_url}
                        alt={title.rendered}
                        loading="lazy"
                        onError={(e) => {
                            e.target.style.display = 'none';
                        }}
                    />
                </div>
            )}
            
            <div className={CSS_CLASSES.postContent}>
                <h3 className={CSS_CLASSES.postTitle}>
                    <a 
                        href={link} 
                        dangerouslySetInnerHTML={{ __html: title.rendered }}
                        rel="bookmark"
                    />
                </h3>
                
                {showExcerpt && excerpt && (
                    <div className={CSS_CLASSES.postExcerpt}>
                        <div 
                            dangerouslySetInnerHTML={{ 
                                __html: excerpt.rendered.substring(0, excerptLength) + '...' 
                            }} 
                        />
                    </div>
                )}
                
                <div className={CSS_CLASSES.postMeta}>
                    {showDate && (
                        <time 
                            className={CSS_CLASSES.postDate} 
                            dateTime={date}
                            title={new Date(date).toLocaleString()}
                        >
                            {new Date(date).toLocaleDateString()}
                        </time>
                    )}
                    
                    {showAuthor && author && (
                        <span className={CSS_CLASSES.postAuthor}>
                            {__('By', 'blockxpert')} {author.name}
                        </span>
                    )}
                </div>
                
                {showReadMore && (
                    <a 
                        href={link} 
                        className={CSS_CLASSES.readMoreLink}
                        aria-label={`${__('Read more about', 'blockxpert')} ${title.rendered}`}
                    >
                        {__('Read More', 'blockxpert')}
                        <span className="blockxpert-apb-read-more-arrow">→</span>
                    </a>
                )}
            </div>
        </article>
    );
}
