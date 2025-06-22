import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    RangeControl, 
    ToggleControl, 
    SelectControl,
    Placeholder,
    Button,
    Notice,
    Spinner
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes }) {
    const [loading, setLoading] = useState(false);
    const [aiResponse, setAiResponse] = useState('');
    const [products, setProducts] = useState([]);
    const [currentProduct, setCurrentProduct] = useState(null);

    const {
        title,
        aiEnabled,
        recommendationType,
        layoutStyle,
        productsCount,
        theme,
        showPrice,
        showRating,
        showAddToCart,
        inStockOnly,
        excludeCurrent,
        priceRange,
        customPrompt,
        apiKey,
        model,
        cacheEnabled,
        cacheDuration,
        currentProductId,
        recommendedProducts
    } = attributes;

    // Get current post ID to detect if we're on a product page
    const postId = useSelect(select => {
        return select('core/editor')?.getCurrentPostId() || 0;
    }, []);

    // Get current post type
    const postType = useSelect(select => {
        return select('core/editor')?.getCurrentPostType() || '';
    }, []);

    // Fetch current product data if on a product page
    useEffect(() => {
        if (postType === 'product' && postId) {
            setCurrentProductId(postId);
            fetchCurrentProduct(postId);
        }
    }, [postId, postType]);

    const fetchCurrentProduct = async (productId) => {
        if (window.wp && window.wp.apiFetch) {
            try {
                const response = await window.wp.apiFetch({ 
                    path: `/wc/v3/products/${productId}` 
                });
                setCurrentProduct(response);
            } catch (error) {
                console.error('Error fetching current product:', error);
            }
        }
    };

    // Fetch sample products for preview
    useEffect(() => {
        if (window.wp && window.wp.apiFetch) {
            fetchSampleProducts();
        }
    }, []);

    const fetchSampleProducts = async () => {
        try {
            const response = await window.wp.apiFetch({ 
                path: '/wc/v3/products?per_page=8&status=publish' 
            });
            setProducts(response);
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    };

    const generateAIRecommendations = async () => {
        if (!apiKey) {
            alert(__('Please enter your OpenAI API key in the block settings.', 'blockxpert'));
            return;
        }

        if (!currentProduct && postType === 'product') {
            alert(__('Please wait for the current product to load, then try again.', 'blockxpert'));
            return;
        }

        setLoading(true);
        try {
            const response = await fetch('/wp-json/blockxpert/v1/generate-product-recommendations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify({
                    apiKey,
                    model,
                    currentProduct,
                    recommendationType,
                    productsCount,
                    customPrompt,
                    priceRange,
                    inStockOnly
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.products) {
                    setAttributes({ recommendedProducts: data.products });
                    setAiResponse(__('AI recommendations generated successfully!', 'blockxpert'));
                } else {
                    setAiResponse(__('Failed to generate recommendations. Please check your API key.', 'blockxpert'));
                }
            } else {
                setAiResponse(__('Error generating recommendations. Please try again.', 'blockxpert'));
            }
        } catch (error) {
            console.error('Error generating recommendations:', error);
            setAiResponse(__('Error generating recommendations. Please try again.', 'blockxpert'));
        } finally {
            setLoading(false);
        }
    };

    const getRecommendationTypeLabel = (type) => {
        const types = {
            'related': __('Related Products', 'blockxpert'),
            'cross-sell': __('Cross-Sells', 'blockxpert'),
            'upsell': __('Upsells', 'blockxpert'),
            'custom': __('Custom Recommendations', 'blockxpert')
        };
        return types[type] || type;
    };

    const getLayoutStyleLabel = (style) => {
        const styles = {
            'grid': __('Grid', 'blockxpert'),
            'list': __('List', 'blockxpert'),
            'slider': __('Slider', 'blockxpert')
        };
        return styles[style] || style;
    };

    const renderProductCard = (product, index) => {
        const isSlider = layoutStyle === 'slider';
        const cardClass = `product-card ${isSlider ? 'slider-item' : ''}`;
        
        return (
            <div key={product.id || index} className={cardClass}>
                <div className="product-image">
                    {product.images && product.images[0] ? (
                        <img 
                            src={product.images[0].src} 
                            alt={product.name}
                            className="product-thumbnail"
                        />
                    ) : (
                        <div className="product-placeholder">
                            {__('No Image', 'blockxpert')}
                        </div>
                    )}
                </div>
                <div className="product-info">
                    <h3 className="product-title">{product.name}</h3>
                    {showPrice && (
                        <div className="product-price">
                            {product.price_html ? (
                                <div dangerouslySetInnerHTML={{ __html: product.price_html }} />
                            ) : (
                                product.price || __('Price not available', 'blockxpert')
                            )}
                        </div>
                    )}
                    {showRating && product.average_rating && (
                        <div className="product-rating">
                            {'★'.repeat(Math.round(product.average_rating))}
                            <span className="rating-count">({product.review_count})</span>
                        </div>
                    )}
                    {showAddToCart && product.stock_status === 'instock' && (
                        <Button 
                            isPrimary 
                            className="add-to-cart-btn"
                        >
                            {__('Add to Cart', 'blockxpert')}
                        </Button>
                    )}
                </div>
            </div>
        );
    };

    const displayProducts = recommendedProducts.length > 0 ? recommendedProducts : products.slice(0, productsCount);

    return (
        <div {...useBlockProps({ className: `ai-product-recommendations theme-${theme}` })}>
            <InspectorControls>
                <PanelBody title={__('Recommendation Settings', 'blockxpert')} initialOpen={true}>
                    <TextControl
                        label={__('Section Title', 'blockxpert')}
                        value={title}
                        onChange={(title) => setAttributes({ title })}
                        placeholder={__('Enter section title...', 'blockxpert')}
                    />
                    
                    <ToggleControl
                        label={__('Enable AI Recommendations', 'blockxpert')}
                        checked={aiEnabled}
                        onChange={(aiEnabled) => setAttributes({ aiEnabled })}
                    />
                    
                    <SelectControl
                        label={__('Recommendation Type', 'blockxpert')}
                        value={recommendationType}
                        options={[
                            { label: __('Related Products', 'blockxpert'), value: 'related' },
                            { label: __('Cross-Sells', 'blockxpert'), value: 'cross-sell' },
                            { label: __('Upsells', 'blockxpert'), value: 'upsell' },
                            { label: __('Custom', 'blockxpert'), value: 'custom' }
                        ]}
                        onChange={(recommendationType) => setAttributes({ recommendationType })}
                    />
                    
                    {recommendationType === 'custom' && (
                        <TextControl
                            label={__('Custom Prompt', 'blockxpert')}
                            value={customPrompt}
                            onChange={(customPrompt) => setAttributes({ customPrompt })}
                            placeholder={__('e.g., "gift ideas", "trending items", "best sellers"', 'blockxpert')}
                        />
                    )}
                </PanelBody>
                
                <PanelBody title={__('Display Settings', 'blockxpert')} initialOpen={false}>
                    <SelectControl
                        label={__('Layout Style', 'blockxpert')}
                        value={layoutStyle}
                        options={[
                            { label: __('Grid', 'blockxpert'), value: 'grid' },
                            { label: __('List', 'blockxpert'), value: 'list' },
                            { label: __('Slider', 'blockxpert'), value: 'slider' }
                        ]}
                        onChange={(layoutStyle) => setAttributes({ layoutStyle })}
                    />
                    
                    <RangeControl
                        label={__('Number of Products', 'blockxpert')}
                        value={productsCount}
                        onChange={(productsCount) => setAttributes({ productsCount })}
                        min={1}
                        max={12}
                    />
                    
                    <SelectControl
                        label={__('Theme', 'blockxpert')}
                        value={theme}
                        options={[
                            { label: __('Light', 'blockxpert'), value: 'light' },
                            { label: __('Dark', 'blockxpert'), value: 'dark' },
                            { label: __('Minimal', 'blockxpert'), value: 'minimal' }
                        ]}
                        onChange={(theme) => setAttributes({ theme })}
                    />
                </PanelBody>
                
                <PanelBody title={__('Product Display', 'blockxpert')} initialOpen={false}>
                    <ToggleControl
                        label={__('Show Price', 'blockxpert')}
                        checked={showPrice}
                        onChange={(showPrice) => setAttributes({ showPrice })}
                    />
                    
                    <ToggleControl
                        label={__('Show Rating', 'blockxpert')}
                        checked={showRating}
                        onChange={(showRating) => setAttributes({ showRating })}
                    />
                    
                    <ToggleControl
                        label={__('Show Add to Cart Button', 'blockxpert')}
                        checked={showAddToCart}
                        onChange={(showAddToCart) => setAttributes({ showAddToCart })}
                    />
                </PanelBody>
                
                <PanelBody title={__('AI Settings', 'blockxpert')} initialOpen={false}>
                    <TextControl
                        label={__('OpenAI API Key', 'blockxpert')}
                        value={apiKey}
                        onChange={(apiKey) => setAttributes({ apiKey })}
                        type="password"
                        placeholder={__('Enter your OpenAI API key...', 'blockxpert')}
                    />
                    
                    <SelectControl
                        label={__('AI Model', 'blockxpert')}
                        value={model}
                        options={[
                            { label: 'GPT-3.5 Turbo', value: 'gpt-3.5-turbo' },
                            { label: 'GPT-4', value: 'gpt-4' },
                            { label: 'GPT-4 Turbo', value: 'gpt-4-turbo-preview' }
                        ]}
                        onChange={(model) => setAttributes({ model })}
                    />
                    
                    <ToggleControl
                        label={__('In Stock Only', 'blockxpert')}
                        checked={inStockOnly}
                        onChange={(inStockOnly) => setAttributes({ inStockOnly })}
                    />
                    
                    <ToggleControl
                        label={__('Exclude Current Product', 'blockxpert')}
                        checked={excludeCurrent}
                        onChange={(excludeCurrent) => setAttributes({ excludeCurrent })}
                    />
                    
                    {aiEnabled && (
                        <Button
                            isPrimary
                            onClick={generateAIRecommendations}
                            isBusy={loading}
                            disabled={!apiKey}
                        >
                            {loading ? __('Generating...', 'blockxpert') : __('Generate AI Recommendations', 'blockxpert')}
                        </Button>
                    )}
                </PanelBody>
            </InspectorControls>

            <div className="ai-product-recommendations-editor">
                <h2 className="recommendations-title">{title || __('AI Product Recommendations', 'blockxpert')}</h2>
                
                <style>
                    {`
                        .ai-product-recommendations-editor .product-price {
                            font-size: 1.2rem;
                            font-weight: 600;
                            color: #007cba;
                            margin-bottom: 0.5rem;
                        }
                        .ai-product-recommendations-editor .woocommerce-Price-amount {
                            font-weight: 600;
                        }
                        .ai-product-recommendations-editor .woocommerce-Price-currencySymbol {
                            font-weight: normal;
                        }
                        .ai-product-recommendations-editor .product-card {
                            border: 1px solid #e1e5e9;
                            border-radius: 8px;
                            overflow: hidden;
                            transition: all 0.3s ease;
                            background: #fff;
                            margin-bottom: 1rem;
                        }
                        .ai-product-recommendations-editor .product-image {
                            position: relative;
                            overflow: hidden;
                        }
                        .ai-product-recommendations-editor .product-thumbnail {
                            width: 100%;
                            height: 200px;
                            object-fit: cover;
                        }
                        .ai-product-recommendations-editor .product-info {
                            padding: 1rem;
                        }
                        .ai-product-recommendations-editor .product-title {
                            margin: 0 0 0.5rem 0;
                            font-size: 1.1rem;
                            font-weight: 500;
                        }
                        .ai-product-recommendations-editor .product-rating {
                            margin-bottom: 0.5rem;
                        }
                        .ai-product-recommendations-editor .add-to-cart-btn {
                            width: 100%;
                            margin-top: 0.5rem;
                        }
                        .ai-product-recommendations-editor .products-grid {
                            display: grid;
                            gap: 1.5rem;
                        }
                        .ai-product-recommendations-editor .products-grid.layout-grid {
                            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                        }
                        .ai-product-recommendations-editor .products-grid.layout-list {
                            grid-template-columns: 1fr;
                        }
                        .ai-product-recommendations-editor .products-grid.layout-slider {
                            display: flex;
                            overflow-x: auto;
                            gap: 1rem;
                            padding: 1rem 0;
                        }
                        .ai-product-recommendations-editor .products-grid.layout-slider .product-card {
                            flex: 0 0 250px;
                        }
                    `}
                </style>
                
                {aiResponse && (
                    <Notice 
                        status={aiResponse.includes('successfully') ? 'success' : 'error'}
                        onRemove={() => setAiResponse('')}
                    >
                        {aiResponse}
                    </Notice>
                )}
                
                {currentProduct && (
                    <div className="current-product-info">
                        <h4>{__('Current Product:', 'blockxpert')}</h4>
                        <p>{currentProduct.name}</p>
                        <p>{__('Type:', 'blockxpert')} {getRecommendationTypeLabel(recommendationType)}</p>
                    </div>
                )}
                
                {displayProducts.length === 0 ? (
                    <Placeholder
                        icon="products"
                        label={__('No Products Found', 'blockxpert')}
                        instructions={__('No products available for recommendations. Add products to your store or generate AI recommendations.', 'blockxpert')}
                    >
                        {aiEnabled && (
                            <Button
                                isPrimary
                                onClick={generateAIRecommendations}
                                isBusy={loading}
                                disabled={!apiKey}
                            >
                                {__('Generate AI Recommendations', 'blockxpert')}
                            </Button>
                        )}
                    </Placeholder>
                ) : (
                    <div className={`recommendations-container layout-${layoutStyle}`}>
                        <div className={`products-grid layout-${layoutStyle}`}>
                            {displayProducts.map((product, index) => renderProductCard(product, index))}
                        </div>
                        
                        {aiEnabled && (
                            <div className="ai-actions">
                                <Button
                                    onClick={generateAIRecommendations}
                                    isBusy={loading}
                                    disabled={!apiKey}
                                >
                                    {__('Regenerate AI Recommendations', 'blockxpert')}
                                </Button>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
} 