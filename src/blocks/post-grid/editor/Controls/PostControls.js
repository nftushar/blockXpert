/**
 * Post Controls Component
 * Handles post query and content-related settings
 */

import { 
    PanelBody, 
    SelectControl, 
    CheckboxControl,
    TextControl,
    __experimentalQueryControls as QueryControls
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { ORDER_BY_OPTIONS, ORDER_OPTIONS } from '../../settings/attributes';
import { API_ENDPOINTS } from '../../settings/constants';

export default function PostControls({ attributes, setAttributes }) {
    const {
        postType,
        categories,
        tags,
        orderBy,
        order
    } = attributes;

    const [availableCategories, setAvailableCategories] = useState([]);
    const [availableTags, setAvailableTags] = useState([]);
    const [loading, setLoading] = useState(false);

    // Fetch categories and tags
    useEffect(() => {
        const fetchTaxonomies = async () => {
            setLoading(true);
            try {
                const [categoriesResponse, tagsResponse] = await Promise.all([
                    fetch(`${window.wpApiSettings.root}${API_ENDPOINTS.categories}`),
                    fetch(`${window.wpApiSettings.root}${API_ENDPOINTS.tags}`)
                ]);

                const categoriesData = await categoriesResponse.json();
                const tagsData = await tagsResponse.json();

                setAvailableCategories(categoriesData);
                setAvailableTags(tagsData);
            } catch (error) {
                console.error('Error fetching taxonomies:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchTaxonomies();
    }, []);

    const handlePostTypeChange = (newPostType) => {
        setAttributes({ postType: newPostType });
    };

    const handleCategoryChange = (categoryId, checked) => {
        const newCategories = checked 
            ? [...categories, categoryId]
            : categories.filter(id => id !== categoryId);
        setAttributes({ categories: newCategories });
    };

    const handleTagChange = (tagId, checked) => {
        const newTags = checked 
            ? [...tags, tagId]
            : tags.filter(id => id !== tagId);
        setAttributes({ tags: newTags });
    };

    const handleOrderByChange = (newOrderBy) => {
        setAttributes({ orderBy: newOrderBy });
    };

    const handleOrderChange = (newOrder) => {
        setAttributes({ order: newOrder });
    };

    const categoryOptions = availableCategories.map(cat => ({
        label: cat.name,
        value: cat.id,
        checked: categories.includes(cat.id)
    }));

    const tagOptions = availableTags.map(tag => ({
        label: tag.name,
        value: tag.id,
        checked: tags.includes(tag.id)
    }));

    return (
        <PanelBody 
            title={__('Post Query Settings', 'blockxpert')} 
            initialOpen={false}
            className="blockxpert-apb-post-controls"
        >
            <div className="blockxpert-apb-query-section">
                <h4 className="blockxpert-apb-section-title">
                    {__('Content Source', 'blockxpert')}
                </h4>
                
                <SelectControl
                    label={__('Post Type', 'blockxpert')}
                    value={postType}
                    options={[
                        { label: 'Posts', value: 'post' },
                        { label: 'Pages', value: 'page' },
                        { label: 'Custom Post Type', value: 'custom' }
                    ]}
                    onChange={handlePostTypeChange}
                    help={__('Select the type of content to display', 'blockxpert')}
                />
            </div>

            <div className="blockxpert-apb-filter-section">
                <h4 className="blockxpert-apb-section-title">
                    {__('Content Filters', 'blockxpert')}
                </h4>
                
                {loading ? (
                    <p>{__('Loading categories and tags...', 'blockxpert')}</p>
                ) : (
                    <>
                        <div className="blockxpert-apb-categories">
                            <label className="blockxpert-apb-control-label">
                                {__('Categories', 'blockxpert')}
                            </label>
                            <div className="blockxpert-apb-checkbox-group">
                                {categoryOptions.map(option => (
                                    <CheckboxControl
                                        key={option.value}
                                        label={option.label}
                                        checked={option.checked}
                                        onChange={(checked) => handleCategoryChange(option.value, checked)}
                                    />
                                ))}
                            </div>
                        </div>

                        <div className="blockxpert-apb-tags">
                            <label className="blockxpert-apb-control-label">
                                {__('Tags', 'blockxpert')}
                            </label>
                            <div className="blockxpert-apb-checkbox-group">
                                {tagOptions.map(option => (
                                    <CheckboxControl
                                        key={option.value}
                                        label={option.label}
                                        checked={option.checked}
                                        onChange={(checked) => handleTagChange(option.value, checked)}
                                    />
                                ))}
                            </div>
                        </div>
                    </>
                )}
            </div>

            <div className="blockxpert-apb-sorting-section">
                <h4 className="blockxpert-apb-section-title">
                    {__('Sorting Options', 'blockxpert')}
                </h4>
                
                <SelectControl
                    label={__('Order By', 'blockxpert')}
                    value={orderBy}
                    options={ORDER_BY_OPTIONS}
                    onChange={handleOrderByChange}
                    help={__('How to sort the posts', 'blockxpert')}
                />

                <SelectControl
                    label={__('Order', 'blockxpert')}
                    value={order}
                    options={ORDER_OPTIONS}
                    onChange={handleOrderChange}
                    help={__('Sort order direction', 'blockxpert')}
                />
            </div>

            <div className="blockxpert-apb-query-preview">
                <p className="blockxpert-apb-preview-label">
                    {__('Query Preview:', 'blockxpert')}
                </p>
                <div className="blockxpert-apb-preview-details">
                    <p><strong>{__('Post Type:', 'blockxpert')}</strong> {postType}</p>
                    <p><strong>{__('Categories:', 'blockxpert')}</strong> {categories.length}</p>
                    <p><strong>{__('Tags:', 'blockxpert')}</strong> {tags.length}</p>
                    <p><strong>{__('Order:', 'blockxpert')}</strong> {orderBy} {order}</p>
                </div>
            </div>
        </PanelBody>
    );
}
