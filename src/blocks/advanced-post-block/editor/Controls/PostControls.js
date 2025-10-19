import { useState, useEffect } from '@wordpress/element';
import { SelectControl, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

export default function PostControls({ attributes, setAttributes }) {
    const [categories, setCategories] = useState([]);

    const categoryList = useSelect((select) => {
        return select(coreStore).getEntityRecords('taxonomy', 'category', {
            per_page: -1,
        });
    }, []);

    useEffect(() => {
        if (categoryList) {
            const options = categoryList.map((category) => ({
                label: category.name,
                value: category.id.toString(),
            }));
            options.unshift({ label: __('All Categories', 'BlockXpert'), value: '' });
            setCategories(options);
        }
    }, [categoryList]);

    return (
        <>
            <SelectControl
                label={__('Category', 'BlockXpert')}
                value={attributes.category}
                options={categories}
                onChange={(category) => setAttributes({ category })}
            />

            <RangeControl
                label={__('Number of Posts', 'BlockXpert')}
                value={attributes.postsToShow}
                onChange={(postsToShow) => setAttributes({ postsToShow })}
                min={1}
                max={50}
            />
        </>
    );
}