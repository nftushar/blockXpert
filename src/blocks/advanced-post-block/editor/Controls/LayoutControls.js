import { SelectControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function LayoutControls({ attributes, setAttributes }) {
    return (
        <>
            <SelectControl
                label={__('Layout Style', 'BlockXpert')}
                value={attributes.layout}
                options={[
                    { label: __('Grid', 'BlockXpert'), value: 'grid' },
                    { label: __('Masonry', 'BlockXpert'), value: 'masonry' },
                    { label: __('Slider', 'BlockXpert'), value: 'slider' },
                    { label: __('Ticker', 'BlockXpert'), value: 'ticker' },
                ]}
                onChange={(layout) => setAttributes({ layout })}
            />

            {attributes.layout === 'grid' && (
                <RangeControl
                    label={__('Columns', 'BlockXpert')}
                    value={attributes.columns}
                    onChange={(columns) => setAttributes({ columns })}
                    min={1}
                    max={6}
                />
            )}
        </>
    );
}