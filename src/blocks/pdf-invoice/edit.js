import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__('Button Settings', 'blockxpert')}>
          <TextControl
            label={__('Button Text', 'blockxpert')}
            value={attributes.buttonText}
            onChange={(buttonText) => setAttributes({ buttonText })}
          />
          <ToggleControl
            label={__('Show Order ID Field (for testing)', 'blockxpert')}
            checked={attributes.showOrderIdField}
            onChange={(showOrderIdField) => setAttributes({ showOrderIdField })}
          />
        </PanelBody>
      </InspectorControls>
      <button className="blockxpert-pdf-invoice-btn">{attributes.buttonText}</button>
      {attributes.showOrderIdField && (
        <input type="text" placeholder={__('Order ID', 'blockxpert')} style={{ marginLeft: 8 }} />
      )}
    </div>
  );
} 