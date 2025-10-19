import { useEffect } from '@wordpress/element';
import {
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl,
    ToggleControl,
    Spinner
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { usePostData } from './hooks/usePostData';
import { usePostLayout } from './hooks/usePostLayout';
import LayoutControls from './Controls/LayoutControls';
import DisplayControls from './Controls/DisplayControls';
import PostControls from './Controls/PostControls';
import { PostPreview } from './Preview/PostPreview';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: `layout-${attributes.layout || 'grid'} columns-${attributes.columns || 3}`
    });
    const { posts, loading, error } = usePostData(attributes);
    const layoutConfig = usePostLayout(attributes);

    if (error) {
        return <div {...blockProps}>
            <p className="components-notice is-error">
                {error}
            </p>
        </div>;
    }

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Layout Settings', 'BlockXpert')}>
                    <LayoutControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                    />
                </PanelBody>

                <PanelBody title={__('Post Settings', 'BlockXpert')}>
                    <PostControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                    />
                </PanelBody>

                <PanelBody title={__('Display Settings', 'BlockXpert')}>
                    <DisplayControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                    />
                </PanelBody>

                {(attributes.layout === 'slider' || attributes.layout === 'ticker') && (
                    <PanelBody title={__('Animation Settings', 'BlockXpert')}>
                        {attributes.layout === 'slider' && (
                            <>
                                <ToggleControl
                                    label={__('Auto Play', 'BlockXpert')}
                                    checked={attributes.sliderOptions.autoplay}
                                    onChange={(autoplay) =>
                                        setAttributes({
                                            sliderOptions: {
                                                ...attributes.sliderOptions,
                                                autoplay,
                                            },
                                        })
                                    }
                                />
                                <RangeControl
                                    label={__('Slide Duration (ms)', 'BlockXpert')}
                                    value={attributes.sliderOptions.speed}
                                    onChange={(speed) =>
                                        setAttributes({
                                            sliderOptions: {
                                                ...attributes.sliderOptions,
                                                speed,
                                            },
                                        })
                                    }
                                    min={1000}
                                    max={10000}
                                    step={500}
                                />
                            </>
                        )}
                        {attributes.layout === 'ticker' && (
                            <RangeControl
                                label={__('Ticker Speed', 'BlockXpert')}
                                value={attributes.tickerOptions.speed}
                                onChange={(speed) =>
                                    setAttributes({
                                        tickerOptions: {
                                            ...attributes.tickerOptions,
                                            speed,
                                        },
                                    })
                                }
                                min={10}
                                max={200}
                                step={5}
                            />
                        )}
                    </PanelBody>
                )}
            </InspectorControls>

            <div {...blockProps}>
                {loading ? (
                    <div className="wp-block-blockxpert-advanced-post-block__loading">
                        <Spinner />
                    </div>
                ) : (
                    <PostPreview
                        posts={posts}
                        attributes={attributes}
                        layoutConfig={layoutConfig}
                    />
                )}
            </div>
        </>
    );
}