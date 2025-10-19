/**
 * Default Settings for Advanced Post Block
 * Provides default configuration and fallback values
 */

import { DEFAULT_SETTINGS, CSS_CLASSES } from './constants';

export const getDefaultSettings = () => ({
    ...DEFAULT_SETTINGS
});

export const getDefaultAttributes = () => {
    const attributes = {};
    
    Object.keys(DEFAULT_SETTINGS).forEach(key => {
        attributes[key] = {
            type: typeof DEFAULT_SETTINGS[key],
            default: DEFAULT_SETTINGS[key]
        };
    });
    
    return attributes;
};

export const getDefaultSupports = () => ({
    align: ['wide', 'full'],
    spacing: {
        padding: true,
        margin: true
    },
    color: {
        background: true,
        text: true,
        link: true
    },
    typography: {
        fontSize: true,
        lineHeight: true,
        fontFamily: true,
        fontWeight: true
    }
});

export const getDefaultEditorSettings = () => ({
    showLayoutControls: true,
    showDisplayControls: true,
    showContentControls: true,
    showQueryControls: true,
    showStyleControls: true,
    enablePreview: true,
    enableResponsivePreview: true
});

export const getDefaultFrontendSettings = () => ({
    enableLazyLoading: true,
    enableInfiniteScroll: false,
    enableAjaxPagination: false,
    enableSmoothScrolling: true,
    enableTouchGestures: true
});
