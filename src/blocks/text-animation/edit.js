import { __ } from '@wordpress/i18n';
import {
  PanelBody,
  TextControl,
  RangeControl,
  SelectControl,
  ToggleControl,
  ColorPalette,
} from '@wordpress/components';
import {
  InspectorControls,
  useBlockProps,
  ColorEdit,
} from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import './editor.scss';

const colors = [
  { name: 'White', color: '#FFFFFF' },
  { name: 'Purple', color: '#6C5CE7' },
  { name: 'Blue', color: '#0984E3' },
  { name: 'Green', color: '#00B894' },
  { name: 'Orange', color: '#FDCB6E' },
  { name: 'Red', color: '#FF7675' },
  { name: 'Black', color: '#000000' },
  { name: 'Gray', color: '#95A5A6' },
];

export default function Edit({ attributes, setAttributes }) {
  const {
    headline,
    headlineColor,
    headlineAnimColor,
    tagline,
    taglineColor,
    animationType,
    duration,
    stagger,
    ease,
    minHeight,
    alignment,
    enableBlur,
  } = attributes;

  const blockProps = useBlockProps({
    style: {
      minHeight: `${minHeight}px`,
      display: 'flex',
      alignItems: 'center',
      justifyContent: alignment === 'center' ? 'center' : alignment === 'left' ? 'flex-start' : 'flex-end',
      flexDirection: 'column',
    },
  });

  const easeOptions = [
    { label: __('Exponential Out', 'blockxpert'), value: 'expo.out' },
    { label: __('Power 2 Out', 'blockxpert'), value: 'power2.out' },
    { label: __('Power 3 Out', 'blockxpert'), value: 'power3.out' },
    { label: __('Back Out', 'blockxpert'), value: 'back.out' },
    { label: __('Elastic Out', 'blockxpert'), value: 'elastic.out' },
  ];

  return (
    <>
      <InspectorControls>
        {/* Text Content Panel */}
        <PanelBody title={__('Text Content', 'blockxpert')} initialOpen={true}>
          <TextControl
            label={__('Headline', 'blockxpert')}
            value={headline}
            onChange={(value) => setAttributes({ headline: value })}
            placeholder={__('Enter your headline', 'blockxpert')}
          />
          <TextControl
            label={__('Tagline', 'blockxpert')}
            value={tagline}
            onChange={(value) => setAttributes({ tagline: value })}
            placeholder={__('Enter your tagline', 'blockxpert')}
          />
        </PanelBody>

        {/* Animation Settings Panel */}
        <PanelBody title={__('Animation Settings', 'blockxpert')} initialOpen={false}>
          <SelectControl
            label={__('Animation Type', 'blockxpert')}
            value={animationType}
            options={[
              { label: __('Character by Character', 'blockxpert'), value: 'characters' },
              { label: __('Word by Word', 'blockxpert'), value: 'words' },
              { label: __('Line by Line', 'blockxpert'), value: 'lines' },
            ]}
            onChange={(value) => setAttributes({ animationType: value })}
          />
          <RangeControl
            label={__('Duration (seconds)', 'blockxpert')}
            value={duration}
            onChange={(value) => setAttributes({ duration: value })}
            min={0.3}
            max={2}
            step={0.1}
          />
          <RangeControl
            label={__('Stagger Delay', 'blockxpert')}
            value={stagger}
            onChange={(value) => setAttributes({ stagger: value })}
            min={0}
            max={0.5}
            step={0.01}
          />
          <SelectControl
            label={__('Easing Function', 'blockxpert')}
            value={ease}
            options={easeOptions}
            onChange={(value) => setAttributes({ ease: value })}
          />
          <ToggleControl
            label={__('Enable Blur Effect', 'blockxpert')}
            checked={enableBlur}
            onChange={(value) => setAttributes({ enableBlur: value })}
          />
        </PanelBody>

        {/* Color Settings Panel */}
        <PanelBody title={__('Colors', 'blockxpert')} initialOpen={false}>
          <p className="components-label">{__('Headline Color', 'blockxpert')}</p>
          <ColorPalette
            colors={colors}
            value={headlineColor}
            onChange={(value) => setAttributes({ headlineColor: value })}
          />

          <p className="components-label" style={{ marginTop: '16px' }}>
            {__('Headline Animation Color', 'blockxpert')}
          </p>
          <ColorPalette
            colors={colors}
            value={headlineAnimColor}
            onChange={(value) => setAttributes({ headlineAnimColor: value })}
          />

          <p className="components-label" style={{ marginTop: '16px' }}>
            {__('Tagline Color', 'blockxpert')}
          </p>
          <ColorPalette
            colors={colors}
            value={taglineColor}
            onChange={(value) => setAttributes({ taglineColor: value })}
          />
        </PanelBody>

        {/* Layout Settings Panel */}
        <PanelBody title={__('Layout', 'blockxpert')} initialOpen={false}>
          <SelectControl
            label={__('Text Alignment', 'blockxpert')}
            value={alignment}
            options={[
              { label: __('Left', 'blockxpert'), value: 'left' },
              { label: __('Center', 'blockxpert'), value: 'center' },
              { label: __('Right', 'blockxpert'), value: 'right' },
            ]}
            onChange={(value) => setAttributes({ alignment: value })}
          />
          <RangeControl
            label={__('Minimum Height (px)', 'blockxpert')}
            value={minHeight}
            onChange={(value) => setAttributes({ minHeight: value })}
            min={200}
            max={800}
            step={50}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div
          className="blockxpert-text-animation-wrapper"
          style={{
            textAlign: alignment,
            width: '100%',
            padding: '40px 20px',
          }}
        >
          {/* Headline */}
          <div className="headline-wrapper" style={{ overflow: 'hidden' }}>
            <h1
              className="main-headline"
              style={{
                color: headlineColor,
                fontSize: 'clamp(2.5rem, 8vw, 6rem)',
                fontWeight: 800,
                lineHeight: 1.3,
                letterSpacing: '-1px',
                margin: '0 0 20px 0',
              }}
            >
              {headline || __('Your Headline Here', 'blockxpert')}
            </h1>
          </div>

          {/* Tagline */}
          <div className="tagline-wrapper" style={{ overflow: 'hidden' }}>
            <p
              className="tagline"
              style={{
                color: taglineColor,
                fontSize: 'clamp(1rem, 3vw, 1.5rem)',
                fontWeight: 400,
                opacity: 0.9,
                margin: 0,
              }}
            >
              {tagline || __('Your tagline here', 'blockxpert')}
            </p>
          </div>

          {/* Preview Info */}
          <div
            style={{
              marginTop: '30px',
              padding: '15px',
              backgroundColor: 'rgba(108, 92, 231, 0.1)',
              borderRadius: '4px',
              fontSize: '12px',
              color: '#666',
            }}
          >
            <p style={{ margin: '0 0 8px 0' }}>
              <strong>{__('Preview:', 'blockxpert')}</strong> {__('Animation will play on frontend', 'blockxpert')}
            </p>
            <p style={{ margin: 0 }}>
              {__('Animation Type:', 'blockxpert')} <strong>{animationType}</strong> | {__('Duration:', 'blockxpert')}{' '}
              <strong>{duration}s</strong> | {__('Ease:', 'blockxpert')} <strong>{ease}</strong>
            </p>
          </div>
        </div>
      </div>
    </>
  );
}
