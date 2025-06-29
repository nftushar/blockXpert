import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl } from '@wordpress/components';

const LAYOUT_OPTIONS = [
  { label: __('Grid', 'blockxpert'), value: 'grid' },
  { label: __('Masonry', 'blockxpert'), value: 'masonry' },
  { label: __('Slider', 'blockxpert'), value: 'slider' },
  { label: __('Ticker', 'blockxpert'), value: 'ticker' },
];

export default function Edit({ attributes, setAttributes }) {
  const {
    layout,
    postsToShow,
    columns,
    showExcerpt,
    showDate,
    showAuthor,
    showImage,
  } = attributes;

  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__('Layout Options', 'blockxpert')} initialOpen={true}>
          <SelectControl
            label={__('Layout', 'blockxpert')}
            value={layout}
            options={LAYOUT_OPTIONS}
            onChange={(value) => setAttributes({ layout: value })}
          />
          {layout === 'grid' || layout === 'masonry' ? (
            <RangeControl
              label={__('Columns', 'blockxpert')}
              value={columns}
              onChange={(value) => setAttributes({ columns: value })}
              min={1}
              max={6}
            />
          ) : null}
          <RangeControl
            label={__('Posts to Show', 'blockxpert')}
            value={postsToShow}
            onChange={(value) => setAttributes({ postsToShow: value })}
            min={1}
            max={20}
          />
          <ToggleControl
            label={__('Show Excerpt', 'blockxpert')}
            checked={showExcerpt}
            onChange={(value) => setAttributes({ showExcerpt: value })}
          />
          <ToggleControl
            label={__('Show Date', 'blockxpert')}
            checked={showDate}
            onChange={(value) => setAttributes({ showDate: value })}
          />
          <ToggleControl
            label={__('Show Author', 'blockxpert')}
            checked={showAuthor}
            onChange={(value) => setAttributes({ showAuthor: value })}
          />
          <ToggleControl
            label={__('Show Image', 'blockxpert')}
            checked={showImage}
            onChange={(value) => setAttributes({ showImage: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div className={`apb-preview apb-layout-${layout}`}>
        <p>{__('Advanced Post Block Preview', 'blockxpert')}</p>
        <p>{__('Layout:', 'blockxpert')} {layout}</p>
      </div>
    </div>
  );
} 