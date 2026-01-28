import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, RangeControl, ToggleControl } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import gsap from 'gsap';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    const sliderRef = useRef();
    const [products, setProducts] = useState([]);

    // Fetch products dynamically
    useEffect(() => {
        const params = { per_page: 12 };
        if (attributes.category) params.category = attributes.category;
        apiFetch({ path: `/wc/v3/products?${new URLSearchParams(params)}` })
            .then(setProducts)
            .catch(() => setProducts([]));
    }, [attributes.category]);

    // Initialize GSAP slider in editor
    useEffect(() => {
        if (!sliderRef.current || !products.length) return;

        const track = sliderRef.current.querySelector('.slider-track');
        const cards = track.querySelectorAll('.product-card');
        let index = 0;

        function goNext() {
            index = (index + 1) % cards.length;
            gsap.to(track, { x: -index * (cards[0].offsetWidth + 10), duration: 0.5 });
        }
        function goPrev() {
            index = (index - 1 + cards.length) % cards.length;
            gsap.to(track, { x: -index * (cards[0].offsetWidth + 10), duration: 0.5 });
        }

        let interval;
        if (attributes.autoPlay) interval = setInterval(goNext, 3000);

        const prevBtn = sliderRef.current.querySelector('.slider-prev');
        const nextBtn = sliderRef.current.querySelector('.slider-next');
        if (prevBtn) prevBtn.onclick = goPrev;
        if (nextBtn) nextBtn.onclick = goNext;

        return () => clearInterval(interval);
    }, [products, attributes.autoPlay]);

    return (
        <div {...blockProps} ref={sliderRef}>
            <InspectorControls>
                <PanelBody title={__('Block Settings', 'blockxpert')} initialOpen>
                    <TextControl
                        label={__('Title', 'blockxpert')}
                        value={attributes.title}
                        onChange={(title) => setAttributes({ title })}
                    />
                    <RangeControl
                        label={__('Products per Slide', 'blockxpert')}
                        value={attributes.productsPerSlide}
                        onChange={(productsPerSlide) => setAttributes({ productsPerSlide })}
                        min={1}
                        max={6}
                    />
                    <ToggleControl
                        label={__('Autoplay', 'blockxpert')}
                        checked={attributes.autoPlay}
                        onChange={(autoPlay) => setAttributes({ autoPlay })}
                    />
                    <ToggleControl
                        label={__('Show Navigation', 'blockxpert')}
                        checked={attributes.showNavigation}
                        onChange={(showNavigation) => setAttributes({ showNavigation })}
                    />
                </PanelBody>
            </InspectorControls>

            <h3>{attributes.title || __('Products Slider', 'blockxpert')}</h3>

            <div className="blockxpert-product-slider" style={{ overflow: 'hidden', position: 'relative' }}>
                <div
                    className="slider-track"
                    style={{ display: 'flex', gap: '10px', transition: 'transform 0.5s ease' }}
                >
                    {products.slice(0, attributes.productsPerSlide * 2).map((product) => (
                        <div
                            key={product.id}
                            className="product-card"
                            style={{
                                minWidth: `${100 / attributes.productsPerSlide}%`,
                                border: '1px solid #ddd',
                                padding: '10px',
                                boxSizing: 'border-box',
                                textAlign: 'center',
                            }}
                        >
                            <img
                                src={product.images?.[0]?.src || ''}
                                alt={product.name}
                                style={{ width: '100%', height: '100px', objectFit: 'cover' }}
                            />
                            <h4 style={{ fontSize: '14px' }}>{product.name}</h4>
                            <p style={{ fontSize: '12px', color: '#333' }} dangerouslySetInnerHTML={{ __html: product.price_html }} />
                        </div>
                    ))}
                </div>

                {attributes.showNavigation && (
                    <>
                        <button className="slider-prev" style={{ position: 'absolute', left: 0, top: '40%' }}>‹</button>
                        <button className="slider-next" style={{ position: 'absolute', right: 0, top: '40%' }}>›</button>
                    </>
                )}
            </div>
        </div>
    );
}
