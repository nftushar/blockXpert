import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    RangeControl, 
    ToggleControl, 
    SelectControl,
    Placeholder
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes }) {
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentSlide, setCurrentSlide] = useState(0);

    const {
        title,
        productsPerSlide,
        autoPlay,
        showNavigation,
        showPagination,
        category,
        orderBy,
        order
    } = attributes;

    const deviceType = useSelect(select => {
        const { __unstableGetPreviewDeviceType } = select(blockEditorStore);
        return __unstableGetPreviewDeviceType ? __unstableGetPreviewDeviceType() : 'Desktop';
    }, []);
    
    // Fetch WooCommerce categories
    useEffect(() => {
        if (window.wp && window.wp.apiFetch) {
            window.wp.apiFetch({ path: '/wc/v3/products/categories?per_page=100' })
                .then(response => {
                    setCategories(response);
                })
                .catch(error => {
                    console.error('Error fetching categories:', error);
                });
        }
    }, []);

    // Fetch and reset slide on changes
    useEffect(() => {
        if (window.wp && window.wp.apiFetch) {
            setLoading(true);
            setCurrentSlide(0); // Reset slider position when products reload
            let path = `/wc/v3/products?per_page=12&status=publish`;
            
            if (category) {
                path += `&category=${category}`;
            }
            
            path += `&orderby=${orderBy}&order=${order}`;

            window.wp.apiFetch({ path })
                .then(response => {
                    setProducts(response);
                    setLoading(false);
                })
                .catch(error => {
                    console.error('Error fetching products:', error);
                    setLoading(false);
                });
        }
    }, [category, orderBy, order]);

    const categoryOptions = [
        { label: __('All Categories', 'blockxpert'), value: '' },
        ...categories.map(cat => ({
            label: cat.name,
            value: cat.id.toString()
        }))
    ];

    const orderByOptions = [
        { label: __('Date', 'blockxpert'), value: 'date' },
        { label: __('Title', 'blockxpert'), value: 'title' },
        { label: __('Price', 'blockxpert'), value: 'price' },
        { label: __('Popularity', 'blockxpert'), value: 'popularity' },
        { label: __('Rating', 'blockxpert'), value: 'rating' }
    ];

    const orderOptions = [
        { label: __('Descending', 'blockxpert'), value: 'desc' },
        { label: __('Ascending', 'blockxpert'), value: 'asc' }
    ];

    const getSlidesPerView = () => {
        if (deviceType === 'Mobile') return 1;
        if (deviceType === 'Tablet') return Math.min(2, productsPerSlide);
        return productsPerSlide;
    };
    
    const slidesPerView = getSlidesPerView();
    const totalSlides = products.length > 0 ? Math.ceil(products.length / slidesPerView) : 0;

    const goToNext = () => {
        setCurrentSlide(prev => (prev + 1) % totalSlides);
    };

    const goToPrev = () => {
        setCurrentSlide(prev => (prev - 1 + totalSlides) % totalSlides);
    };
    
    const goToSlide = (index) => {
        setCurrentSlide(index);
    };

    // Auto-play handler
    useEffect(() => {
        if (!autoPlay || totalSlides <= 1) {
            return;
        }
        const interval = setInterval(goToNext, 5000);
        return () => clearInterval(interval);
    }, [autoPlay, totalSlides]);

    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__('Slider Settings', 'blockxpert')} initialOpen={true}>
                    <TextControl
                        label={__('Slider Title', 'blockxpert')}
                        value={title}
                        onChange={(title) => setAttributes({ title })}
                        placeholder={__('Enter slider title...', 'blockxpert')}
                    />
                    
                    <RangeControl
                        label={__('Products per Slide', 'blockxpert')}
                        value={productsPerSlide}
                        onChange={(productsPerSlide) => setAttributes({ productsPerSlide })}
                        min={1}
                        max={6}
                    />
                    
                    <ToggleControl
                        label={__('Auto Play', 'blockxpert')}
                        checked={autoPlay}
                        onChange={(autoPlay) => setAttributes({ autoPlay })}
                    />
                    
                    <ToggleControl
                        label={__('Show Navigation Arrows', 'blockxpert')}
                        checked={showNavigation}
                        onChange={(showNavigation) => setAttributes({ showNavigation })}
                    />
                    
                    <ToggleControl
                        label={__('Show Pagination Dots', 'blockxpert')}
                        checked={showPagination}
                        onChange={(showPagination) => setAttributes({ showPagination })}
                    />
                </PanelBody>
                
                <PanelBody title={__('Product Settings', 'blockxpert')} initialOpen={false}>
                    <SelectControl
                        label={__('Product Category', 'blockxpert')}
                        value={category}
                        options={categoryOptions}
                        onChange={(category) => setAttributes({ category })}
                    />
                    
                    <SelectControl
                        label={__('Order By', 'blockxpert')}
                        value={orderBy}
                        options={orderByOptions}
                        onChange={(orderBy) => setAttributes({ orderBy })}
                    />
                    
                    <SelectControl
                        label={__('Order', 'blockxpert')}
                        value={order}
                        options={orderOptions}
                        onChange={(order) => setAttributes({ order })}
                    />
                </PanelBody>
            </InspectorControls>

            <div className="product-slider-editor-preview">
                <h3 className="slider-title">{title || __('Product Carousel', 'blockxpert')}</h3>
                
                {loading && (
                    <Placeholder label={__('Loading Products', 'blockxpert')}>
                        <p>{__('Fetching products for the preview...', 'blockxpert')}</p>
                    </Placeholder>
                )}

                {!loading && products.length === 0 && (
                    <Placeholder
                        icon="woocommerce"
                        label={__('No Products Found', 'blockxpert')}
                        instructions={__('No products were found. Please check your product visibility, selected category, or try creating new products.', 'blockxpert')}
                    >
                        <a 
                            className="components-button is-primary"
                            href="/wp-admin/post-new.php?post_type=product"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {__('Create New Product', 'blockxpert')}
                        </a>
                    </Placeholder>
                )}

                {!loading && products.length > 0 && (
                     <div className="slider-container-preview">
                        <div className="slider-wrapper-preview">
                            <div 
                                className="slider-track-preview"
                                style={{
                                    display: 'flex',
                                    transition: 'transform 0.5s ease-in-out',
                                    transform: `translateX(-${currentSlide * 100}%)`,
                                }}
                            >
                                {products.map((product) => (
                                    <div 
                                        key={product.id} 
                                        className="product-item-preview"
                                        style={{
                                            flex: `0 0 ${100 / slidesPerView}%`,
                                            padding: '0 8px',
                                        }}
                                    >
                                        <div className="product-card-preview">
                                            <div className="product-image-preview">
                                                {product.images && product.images[0] ? (
                                                    <img src={product.images[0].src} alt={product.name}/>
                                                ) : (
                                                    <div className="product-image-placeholder">
                                                        <span>{__('No Image', 'blockxpert')}</span>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="product-info-preview">
                                                <h4 className="product-title-preview">{product.name}</h4>
                                                <p className="product-price-preview" dangerouslySetInnerHTML={{ __html: product.price_html || product.price }} />
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        
                        {showNavigation && totalSlides > 1 && (
                            <>
                                <button className="slider-nav-preview prev" onClick={goToPrev}>‹</button>
                                <button className="slider-nav-preview next" onClick={goToNext}>›</button>
                            </>
                        )}
                        
                        {showPagination && totalSlides > 1 && (
                            <div className="slider-pagination-preview">
                                {Array.from({ length: totalSlides }).map((_, index) => (
                                    <button 
                                        key={index} 
                                        className={`dot-preview ${currentSlide === index ? 'active' : ''}`}
                                        onClick={() => goToSlide(index)}
                                        aria-label={`${__('Go to slide', 'blockxpert')} ${index + 1}`}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
} 