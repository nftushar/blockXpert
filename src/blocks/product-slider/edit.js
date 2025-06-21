import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    RangeControl, 
    ToggleControl, 
    SelectControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

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

    // Fetch sample products for preview
    useEffect(() => {
        if (window.wp && window.wp.apiFetch) {
            setLoading(true);
            let path = '/wc/v3/products?per_page=6&status=publish';
            
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
        { label: __('All Categories', 'gblocks'), value: '' },
        ...categories.map(cat => ({
            label: cat.name,
            value: cat.id.toString()
        }))
    ];

    const orderByOptions = [
        { label: __('Date', 'gblocks'), value: 'date' },
        { label: __('Title', 'gblocks'), value: 'title' },
        { label: __('Price', 'gblocks'), value: 'price' },
        { label: __('Popularity', 'gblocks'), value: 'popularity' },
        { label: __('Rating', 'gblocks'), value: 'rating' }
    ];

    const orderOptions = [
        { label: __('Descending', 'gblocks'), value: 'DESC' },
        { label: __('Ascending', 'gblocks'), value: 'ASC' }
    ];

    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__('Slider Settings', 'gblocks')} initialOpen={true}>
                    <TextControl
                        label={__('Slider Title', 'gblocks')}
                        value={title}
                        onChange={(title) => setAttributes({ title })}
                        placeholder={__('Enter slider title...', 'gblocks')}
                    />
                    
                    <RangeControl
                        label={__('Products per Slide', 'gblocks')}
                        value={productsPerSlide}
                        onChange={(productsPerSlide) => setAttributes({ productsPerSlide })}
                        min={1}
                        max={6}
                    />
                    
                    <ToggleControl
                        label={__('Auto Play', 'gblocks')}
                        checked={autoPlay}
                        onChange={(autoPlay) => setAttributes({ autoPlay })}
                    />
                    
                    <ToggleControl
                        label={__('Show Navigation Arrows', 'gblocks')}
                        checked={showNavigation}
                        onChange={(showNavigation) => setAttributes({ showNavigation })}
                    />
                    
                    <ToggleControl
                        label={__('Show Pagination Dots', 'gblocks')}
                        checked={showPagination}
                        onChange={(showPagination) => setAttributes({ showPagination })}
                    />
                </PanelBody>
                
                <PanelBody title={__('Product Settings', 'gblocks')} initialOpen={false}>
                    <SelectControl
                        label={__('Product Category', 'gblocks')}
                        value={category}
                        options={categoryOptions}
                        onChange={(category) => setAttributes({ category })}
                    />
                    
                    <SelectControl
                        label={__('Order By', 'gblocks')}
                        value={orderBy}
                        options={orderByOptions}
                        onChange={(orderBy) => setAttributes({ orderBy })}
                    />
                    
                    <SelectControl
                        label={__('Order', 'gblocks')}
                        value={order}
                        options={orderOptions}
                        onChange={(order) => setAttributes({ order })}
                    />
                </PanelBody>
            </InspectorControls>

            <div className="product-slider-preview">
                <h3 className="slider-title">{title || __('WooCommerce Product Slider', 'gblocks')}</h3>
                
                {loading ? (
                    <div className="slider-loading">
                        <p>{__('Loading products...', 'gblocks')}</p>
                    </div>
                ) : (
                    <div className="slider-container">
                        <div className="slider-products" style={{ display: 'grid', gridTemplateColumns: `repeat(${productsPerSlide}, 1fr)`, gap: '20px' }}>
                            {products.map((product) => (
                                <div key={product.id} className="product-item">
                                    <div className="product-image">
                                        {product.images && product.images[0] ? (
                                            <img 
                                                src={product.images[0].src} 
                                                alt={product.name}
                                                style={{ width: '100%', height: '150px', objectFit: 'cover' }}
                                            />
                                        ) : (
                                            <div style={{ width: '100%', height: '150px', backgroundColor: '#f0f0f0', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                {__('No Image', 'gblocks')}
                                            </div>
                                        )}
                                    </div>
                                    <div className="product-info">
                                        <h4>{product.name}</h4>
                                        <p className="price">{product.price}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                        
                        {showNavigation && (
                            <div className="slider-navigation">
                                <button className="nav-prev">‹</button>
                                <button className="nav-next">›</button>
                            </div>
                        )}
                        
                        {showPagination && (
                            <div className="slider-pagination">
                                <span className="dot active"></span>
                                <span className="dot"></span>
                                <span className="dot"></span>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
} 