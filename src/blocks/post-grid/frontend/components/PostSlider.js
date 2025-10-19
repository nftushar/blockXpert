/**
 * Post Slider Component
 * Slider/carousel layout for displaying posts
 */

import { useState, useEffect, useRef } from '@wordpress/element';
import { CSS_CLASSES } from '../../settings/constants';
import PostCard from './PostCard';

export default function PostSlider({ 
    attributes, 
    posts, 
    containerProps 
}) {
    const { columns, autoPlay = true, showNavigation = true, showPagination = true } = attributes;
    const [currentSlide, setCurrentSlide] = useState(0);
    const [isAutoPlaying, setIsAutoPlaying] = useState(autoPlay);
    const sliderRef = useRef(null);
    const intervalRef = useRef(null);

    const totalSlides = Math.ceil(posts.length / columns);
    const slideWidth = 100 / columns;

    // Auto-play functionality
    useEffect(() => {
        if (isAutoPlaying && totalSlides > 1) {
            intervalRef.current = setInterval(() => {
                setCurrentSlide(prev => (prev + 1) % totalSlides);
            }, 5000);
        }

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, [isAutoPlaying, totalSlides]);

    const goToSlide = (slideIndex) => {
        setCurrentSlide(slideIndex);
    };

    const goToNext = () => {
        setCurrentSlide(prev => (prev + 1) % totalSlides);
    };

    const goToPrev = () => {
        setCurrentSlide(prev => (prev - 1 + totalSlides) % totalSlides);
    };

    const handleMouseEnter = () => {
        setIsAutoPlaying(false);
    };

    const handleMouseLeave = () => {
        setIsAutoPlaying(autoPlay);
    };

    const sliderClasses = [
        CSS_CLASSES.blockContainer,
        CSS_CLASSES.sliderLayout,
        'blockxpert-apb-slider-wrapper'
    ].filter(Boolean).join(' ');

    const trackClasses = [
        'blockxpert-apb-slider-track',
        isAutoPlaying ? 'blockxpert-apb-slider-autoplay' : ''
    ].filter(Boolean).join(' ');

    if (!posts || posts.length === 0) {
        return (
            <div className={sliderClasses}>
                <div className="blockxpert-apb-empty-state">
                    <p>{__('No posts found matching your criteria.', 'blockxpert')}</p>
                </div>
            </div>
        );
    }

    return (
        <div 
            className={sliderClasses}
            style={containerProps.style}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
            {...containerProps}
        >
            <div className="blockxpert-apb-slider-container">
                <div 
                    ref={sliderRef}
                    className={trackClasses}
                    style={{
                        transform: `translateX(-${currentSlide * 100}%)`,
                        width: `${totalSlides * 100}%`
                    }}
                >
                    {Array.from({ length: totalSlides }).map((_, slideIndex) => (
                        <div 
                            key={slideIndex}
                            className="blockxpert-apb-slider-slide"
                            style={{ width: `${100 / totalSlides}%` }}
                        >
                            <div className="blockxpert-apb-slider-content">
                                {posts
                                    .slice(slideIndex * columns, (slideIndex + 1) * columns)
                                    .map((post, index) => (
                                        <div 
                                            key={post.id}
                                            className={CSS_CLASSES.postItem}
                                            style={{ 
                                                width: `${slideWidth}%`,
                                                animationDelay: `${index * 100}ms`
                                            }}
                                        >
                                            <PostCard 
                                                post={post} 
                                                attributes={attributes} 
                                                index={index}
                                            />
                                        </div>
                                    ))
                                }
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {showNavigation && totalSlides > 1 && (
                <>
                    <button 
                        className="blockxpert-apb-slider-nav blockxpert-apb-slider-prev"
                        onClick={goToPrev}
                        aria-label={__('Previous slide', 'blockxpert')}
                    >
                        ‹
                    </button>
                    <button 
                        className="blockxpert-apb-slider-nav blockxpert-apb-slider-next"
                        onClick={goToNext}
                        aria-label={__('Next slide', 'blockxpert')}
                    >
                        ›
                    </button>
                </>
            )}

            {showPagination && totalSlides > 1 && (
                <div className="blockxpert-apb-slider-pagination">
                    {Array.from({ length: totalSlides }).map((_, index) => (
                        <button
                            key={index}
                            className={`blockxpert-apb-slider-dot ${currentSlide === index ? 'active' : ''}`}
                            onClick={() => goToSlide(index)}
                            aria-label={`${__('Go to slide', 'blockxpert')} ${index + 1}`}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
