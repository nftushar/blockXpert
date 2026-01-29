/**
 * Reusable Block Controls Component
 * Common controls used across multiple blocks
 */

import { 
    InspectorControls, 
    PanelColorSettings 
} from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    ToggleControl,
    FontSizePicker,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Common font size options
 */
export const FONT_SIZES = [
    { name: 'Small', slug: 'small', size: '14px' },
    { name: 'Normal', slug: 'normal', size: '16px' },
    { name: 'Large', slug: 'large', size: '20px' },
    { name: 'Extra Large', slug: 'extra-large', size: '24px' },
    { name: 'XXL', slug: 'xxl', size: '32px' },
];

/**
 * Reusable Typography Controls Panel
 */
export function TypographyControls({
    titleLabel = __('Typography Settings', 'blockxpert'),
    settings = [],
    onSettingChange = () => {},
}) {
    return (
        <PanelBody title={titleLabel} initialOpen={false}>
            {settings.map((setting, index) => (
                <div key={index} className="blockxpert-typography-control">
                    <p className="components-base-control__label">{setting.label}</p>
                    <FontSizePicker
                        fontSizes={FONT_SIZES}
                        value={setting.value}
                        onChange={(size) => onSettingChange(setting.key, size)}
                        withSlider
                        __nextHasNoMarginBottom
                    />
                </div>
            ))}
        </PanelBody>
    );
}

/**
 * Reusable Color Controls Panel
 */
export function ColorControls({
    titleLabel = __('Color Settings', 'blockxpert'),
    colorSettings = [],
    onColorChange = () => {},
}) {
    return (
        <PanelColorSettings
            title={titleLabel}
            initialOpen={false}
            colorSettings={colorSettings.map(setting => ({
                value: setting.value,
                onChange: (color) => onColorChange(setting.key, color),
                label: setting.label,
            }))}
        />
    );
}

/**
 * Reusable Basic Settings Panel
 */
export function BasicSettings({
    settings = [],
    onSettingChange = () => {},
}) {
    return (
        <PanelBody title={__('Settings', 'blockxpert')} initialOpen={true}>
            {settings.map((setting, index) => {
                switch (setting.type) {
                    case 'text':
                        return (
                            <TextControl
                                key={index}
                                label={setting.label}
                                value={setting.value}
                                onChange={(value) => onSettingChange(setting.key, value)}
                                placeholder={setting.placeholder}
                                help={setting.help}
                                __next40pxDefaultSize
                            />
                        );
                    case 'toggle':
                        return (
                            <ToggleControl
                                key={index}
                                label={setting.label}
                                checked={setting.value}
                                onChange={(value) => onSettingChange(setting.key, value)}
                                help={setting.help}
                                __nextHasNoMarginBottom
                            />
                        );
                    case 'number':
                        return (
                            <NumberControl
                                key={index}
                                label={setting.label}
                                value={setting.value}
                                onChange={(value) => onSettingChange(setting.key, value)}
                                min={setting.min}
                                max={setting.max}
                                step={setting.step}
                                __next40pxDefaultSize
                            />
                        );
                    default:
                        return null;
                }
            })}
        </PanelBody>
    );
}

export default {
    FONT_SIZES,
    TypographyControls,
    ColorControls,
    BasicSettings,
};
