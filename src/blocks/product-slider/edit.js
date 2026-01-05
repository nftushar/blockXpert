import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
    PanelBody,
    RangeControl,
    ToggleControl,
    TextControl
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    const { title, productsPerSlide, autoPlay, showNavigation, showPagination, category } = attributes;

    return (
        <div {...blockProps}>
            <div className="product-slider-editor">
                <div className="product-slider-editor-preview" data-products-per-slide={productsPerSlide} data-auto-play={autoPlay ? 'true' : 'false'} data-show-navigation={showNavigation ? 'true' : 'false'} data-show-pagination={showPagination ? 'true' : 'false'}>
                    <h3 className="slider-title">{title || __('Featured Products', 'blockxpert')}</h3>
                    <div className="slides-preview">
                        {Array.from({ length: Math.max(1, productsPerSlide) }).map((_, i) => (
                            <div key={i} className="slide-placeholder">{__('Product', 'blockxpert')} {i + 1}</div>
                        ))}
                    </div>
                    <div className="slider-info">
                        <span>{__('Autoplay:', 'blockxpert')} {autoPlay ? __('On', 'blockxpert') : __('Off', 'blockxpert')}</span>
                        <span>{__('Navigation:', 'blockxpert')} {showNavigation ? __('Shown', 'blockxpert') : __('Hidden', 'blockxpert')}</span>
                        <span>{__('Pagination:', 'blockxpert')} {showPagination ? __('Shown', 'blockxpert') : __('Hidden', 'blockxpert')}</span>
                    </div>
                </div>
                <div className="product-slider-editor-controls">
                    <PanelBody title={__('Settings', 'blockxpert')} initialOpen={true}>
                        <TextControl
                            label={__('Title', 'blockxpert')}
                            value={title}
                            onChange={(val) => setAttributes({ title: val })}
                        />
                        <RangeControl
                            label={__('Products Per Slide', 'blockxpert')}
                            value={productsPerSlide}
                            onChange={(val) => setAttributes({ productsPerSlide: val })}
                            min={1}
                            max={6}
                        />
                        <ToggleControl
                            label={__('Autoplay', 'blockxpert')}
                            checked={autoPlay}
                            onChange={(val) => setAttributes({ autoPlay: val })}
                        />
                        <ToggleControl
                            label={__('Show Navigation', 'blockxpert')}
                            checked={showNavigation}
                            onChange={(val) => setAttributes({ showNavigation: val })}
                        />
                        <ToggleControl
                            label={__('Show Pagination', 'blockxpert')}
                            checked={showPagination}
                            onChange={(val) => setAttributes({ showPagination: val })}
                        />
                        <TextControl
                            label={__('Category (slug or id)', 'blockxpert')}
                            value={category}
                            onChange={(val) => setAttributes({ category: val })}
                        />
                    </PanelBody>
                </div>
            </div>
        </div>
    );
}