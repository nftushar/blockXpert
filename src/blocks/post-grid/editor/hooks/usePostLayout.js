/**
 * usePostLayout Hook
 * Manages layout-related logic and responsive behavior
 */

import { useMemo } from '@wordpress/element';
import { CSS_CLASSES, RESPONSIVE_BREAKPOINTS } from '../../settings/constants';

export const usePostLayout = (layout, columns) => {
    const layoutConfig = useMemo(() => {
        const configs = {
            grid: {
                supportsColumns: true,
                supportsResponsive: true,
                defaultColumns: 3,
                maxColumns: 6,
                minColumns: 1
            },
            masonry: {
                supportsColumns: true,
                supportsResponsive: true,
                defaultColumns: 3,
                maxColumns: 6,
                minColumns: 1
            },
            slider: {
                supportsColumns: true,
                supportsResponsive: true,
                defaultColumns: 3,
                maxColumns: 4,
                minColumns: 1
            },
            ticker: {
                supportsColumns: false,
                supportsResponsive: false,
                defaultColumns: 1,
                maxColumns: 1,
                minColumns: 1
            }
        };

        return configs[layout] || configs.grid;
    }, [layout]);

    const getLayoutClasses = () => {
        const classes = [
            CSS_CLASSES.blockLayout,
            `${CSS_CLASSES.blockLayout}--${layout}`,
            `blockxpert-apb-columns-${columns}`
        ];

        if (layout === 'grid') {
            classes.push(CSS_CLASSES.gridLayout);
        } else if (layout === 'masonry') {
            classes.push(CSS_CLASSES.masonryLayout);
        } else if (layout === 'slider') {
            classes.push(CSS_CLASSES.sliderLayout);
        } else if (layout === 'ticker') {
            classes.push(CSS_CLASSES.tickerLayout);
        }

        return classes.filter(Boolean).join(' ');
    };

    const getResponsiveClasses = () => {
        const responsiveClasses = [
            CSS_CLASSES.responsiveMobile,
            CSS_CLASSES.responsiveTablet,
            CSS_CLASSES.responsiveDesktop
        ];

        return responsiveClasses.join(' ');
    };

    const isLayoutSupported = (testLayout) => {
        return ['grid', 'masonry', 'slider', 'ticker'].includes(testLayout);
    };

    const getColumnOptions = () => {
        const options = [];
        for (let i = layoutConfig.minColumns; i <= layoutConfig.maxColumns; i++) {
            options.push({
                label: `${i} ${i === 1 ? 'Column' : 'Columns'}`,
                value: i
            });
        }
        return options;
    };

    const getResponsiveColumns = (deviceType) => {
        const responsiveConfig = {
            mobile: Math.min(1, columns),
            tablet: Math.min(2, columns),
            desktop: columns
        };

        return responsiveConfig[deviceType] || columns;
    };

    const getLayoutStyles = () => {
        const styles = {
            '--blockxpert-apb-columns': columns,
            '--blockxpert-apb-layout': layout
        };

        if (layout === 'slider') {
            styles['--blockxpert-apb-slider-columns'] = columns;
        }

        return styles;
    };

    return {
        layoutConfig,
        getLayoutClasses,
        getResponsiveClasses,
        isLayoutSupported,
        getColumnOptions,
        getResponsiveColumns,
        getLayoutStyles
    };
};
