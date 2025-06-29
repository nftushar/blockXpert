import { useBlockProps, InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    RangeControl, 
    ToggleControl, 
    Placeholder,
    Button,
    TextareaControl,
    Notice,
    FontSizePicker,
    SelectControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

const FONT_SIZES = [
    { name: 'Small', slug: 'small', size: '14px' },
    { name: 'Normal', slug: 'normal', size: '16px' },
    { name: 'Large', slug: 'large', size: '20px' },
    { name: 'Extra Large', slug: 'extra-large', size: '24px' },
];

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    const [loading, setLoading] = useState(false);
    const [aiResponse, setAiResponse] = useState('');
    const [editingQuestion, setEditingQuestion] = useState(null);
    const [expandedQuestions, setExpandedQuestions] = useState(new Set());
    const [searchTerm, setSearchTerm] = useState('');

    const {
        title,
        aiEnabled,
        maxQuestions,
        autoGenerate,
        showSearch,
        accordionStyle,
        questions,
        apiKey,
        model,
        titleColor,
        questionColor,
        answerColor,
        titleFontSize,
        questionFontSize,
        answerFontSize,
        animationType,
        animationDuration
    } = attributes;

    const titleStyle = {
        color: titleColor,
        fontSize: titleFontSize,
    };

    const questionStyle = {
        color: questionColor,
        fontSize: questionFontSize,
    };

    const answerStyle = {
        color: answerColor,
        fontSize: answerFontSize,
    };

    const toggleQuestion = (index) => {
        const newExpanded = new Set(expandedQuestions);
        if (newExpanded.has(index)) {
            newExpanded.delete(index);
        } else {
            newExpanded.add(index);
        }
        setExpandedQuestions(newExpanded);
    };

    const addQuestion = () => {
        const newQuestion = {
            question: '',
            answer: '',
            id: Date.now()
        };
        setAttributes({ questions: [...questions, newQuestion] });
        setEditingQuestion(newQuestion.id);
    };

    const updateQuestion = (index, field, value) => {
        const updatedQuestions = [...questions];
        updatedQuestions[index] = { ...updatedQuestions[index], [field]: value };
        setAttributes({ questions: updatedQuestions });
    };

    const deleteQuestion = (index) => {
        const updatedQuestions = questions.filter((_, i) => i !== index);
        setAttributes({ questions: updatedQuestions });
    };

    const generateAIQuestions = async () => {
        setLoading(true);
        setAiResponse('');

        try {
            // This is a placeholder for your actual API call
            // You would use wp.apiFetch or similar here
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));

            const generatedQuestions = [
                { question: 'What is BlockXpert?', answer: 'BlockXpert is a powerful suite of blocks for the WordPress editor.', id: Date.now() + 1 },
                { question: 'How do I get an API key?', answer: 'You can get an API key from the OpenAI website.', id: Date.now() + 2 }
            ];

            setAttributes({ questions: [...questions, ...generatedQuestions] });
            setAiResponse(__('FAQ questions generated successfully!', 'blockxpert'));
        } catch (error) {
            console.error('Error generating questions:', error);
            setAiResponse(__('Error generating questions. Please try again.', 'blockxpert'));
        }

        setLoading(false);
    };
    
    const filteredQuestions = questions.filter(q => 
        q.question.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('FAQ Settings', 'blockxpert')} initialOpen={true}>
                    <TextControl
                        label={__('FAQ Title', 'blockxpert')}
                        value={title}
                        onChange={(title) => setAttributes({ title })}
                        placeholder={__('Enter FAQ title...', 'blockxpert')}
                    />
                    
                    <ToggleControl
                        label={__('Enable AI Generation', 'blockxpert')}
                        checked={aiEnabled}
                        onChange={(aiEnabled) => setAttributes({ aiEnabled })}
                        __nextHasNoMarginBottom
                    />
                    
                    {aiEnabled && (
                        <ToggleControl
                            label={__('Auto Generate Questions on page load', 'blockxpert')}
                            checked={autoGenerate}
                            onChange={(autoGenerate) => setAttributes({ autoGenerate })}
                            __nextHasNoMarginBottom
                            help={__('Automatically generate questions when the block is added.', 'blockxpert')}
                        />
                    )}
                    
                    <ToggleControl
                        label={__('Show Search Bar', 'blockxpert')}
                        checked={showSearch}
                        onChange={(showSearch) => setAttributes({ showSearch })}
                        __nextHasNoMarginBottom
                    />
                </PanelBody>

                <PanelColorSettings
                    title={__('Color Settings', 'blockxpert')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: titleColor,
                            onChange: (titleColor) => setAttributes({ titleColor }),
                            label: __('Title Color', 'blockxpert'),
                        },
                        {
                            value: questionColor,
                            onChange: (questionColor) => setAttributes({ questionColor }),
                            label: __('Question Color', 'blockxpert'),
                        },
                        {
                            value: answerColor,
                            onChange: (answerColor) => setAttributes({ answerColor }),
                            label: __('Answer Color', 'blockxpert'),
                        },
                    ]}
                />

                <PanelBody title={__('Font Size Settings', 'blockxpert')} initialOpen={false}>
                    <p className="components-base-control__label">{__('Title Font Size', 'blockxpert')}</p>
                    <FontSizePicker
                        fontSizes={FONT_SIZES}
                        value={titleFontSize}
                        onChange={(titleFontSize) => setAttributes({ titleFontSize })}
                        withSlider
                    />
                    <p className="components-base-control__label">{__('Question Font Size', 'blockxpert')}</p>
                    <FontSizePicker
                        fontSizes={FONT_SIZES}
                        value={questionFontSize}
                        onChange={(questionFontSize) => setAttributes({ questionFontSize })}
                        withSlider
                    />
                    <p className="components-base-control__label">{__('Answer Font Size', 'blockxpert')}</p>
                    <FontSizePicker
                        fontSizes={FONT_SIZES}
                        value={answerFontSize}
                        onChange={(answerFontSize) => setAttributes({ answerFontSize })}
                        withSlider
                    />
                </PanelBody>

                <PanelBody title={__('Animation Settings', 'blockxpert')} initialOpen={false}>
                    <RangeControl
                        label={__('Animation Duration (ms)', 'blockxpert')}
                        value={animationDuration}
                        onChange={(animationDuration) => setAttributes({ animationDuration })}
                        min={100}
                        max={1000}
                        step={50}
                    />
                </PanelBody>
                
                {aiEnabled && (
                    <PanelBody title={__('AI Settings', 'blockxpert')} initialOpen={false}>
                        <TextControl
                            label={__('OpenAI API Key', 'blockxpert')}
                            value={apiKey}
                            onChange={(apiKey) => setAttributes({ apiKey })}
                            placeholder={__('Enter your OpenAI API key...', 'blockxpert')}
                            help={!apiKey ? __('An API key is required for AI features.', 'blockxpert') : ''}
                            __nextHasNoMarginBottom
                            __next40pxDefaultSize
                        />
                        <TextControl
                            label={__('AI Model', 'blockxpert')}
                            value={model}
                            onChange={(model) => setAttributes({ model })}
                        />
                        <RangeControl
                            label={__('Maximum Questions to Generate', 'blockxpert')}
                            value={maxQuestions}
                            onChange={(maxQuestions) => setAttributes({ maxQuestions })}
                            min={1}
                            max={10}
                        />
                        <Button
                            isPrimary
                            onClick={generateAIQuestions}
                            isBusy={loading}
                            disabled={!apiKey || loading}
                        >
                            {loading ? __('Generating...', 'blockxpert') : __('Generate AI Questions', 'blockxpert')}
                        </Button>
                    </PanelBody>
                )}
            </InspectorControls>

            <div className="ai-faq-editor">
                <h2 className="faq-title" style={titleStyle}>{title || __('AI FAQ', 'blockxpert')}</h2>
                
                {aiResponse && (
                    <Notice 
                        status={aiResponse.includes('successfully') ? 'success' : 'error'}
                        onRemove={() => setAiResponse('')}
                    >
                        {aiResponse}
                    </Notice>
                )}
                
                {showSearch && (
                    <div className="faq-search">
                        <TextControl
                            placeholder={__('Search questions...', 'blockxpert')}
                            value={searchTerm}
                            onChange={setSearchTerm}
                            __next40pxDefaultSize
                        />
                    </div>
                )}
                
                {!questions || questions.length === 0 ? (
                    <Placeholder
                        icon="editor-help"
                        label={__('No FAQ Questions', 'blockxpert')}
                        instructions={__('Add your first FAQ question or generate AI-powered questions.', 'blockxpert')}
                    >
                        <div className="faq-placeholder-actions">
                            <Button
                                isPrimary
                                onClick={addQuestion}
                            >
                                {__('Add Question', 'blockxpert')}
                            </Button>
                            
                            {aiEnabled && (
                                <Button
                                    onClick={generateAIQuestions}
                                    isBusy={loading}
                                    disabled={!apiKey}
                                >
                                    {__('Generate AI Questions', 'blockxpert')}
                                </Button>
                            )}
                        </div>
                    </Placeholder>
                ) : (
                    <div className="faq-questions">
                        {filteredQuestions.map((question, index) => (
                            <div key={question.id || index} className="faq-question">
                                {editingQuestion === question.id ? (
                                    <div className="faq-edit-form">
                                        <TextControl
                                            label={__('Question', 'blockxpert')}
                                            value={question.question}
                                            onChange={(value) => updateQuestion(index, 'question', value)}
                                            placeholder={__('Enter your question...', 'blockxpert')}
                                        />
                                        <PanelColorSettings
                                            title={__('Question Color', 'blockxpert')}
                                            colorSettings={[{
                                                value: question.questionColor,
                                                onChange: (color) => updateQuestion(index, 'questionColor', color),
                                                label: __('Question Color', 'blockxpert'),
                                            }]}
                                        />
                                        <FontSizePicker
                                            fontSizes={FONT_SIZES}
                                            value={question.questionFontSize}
                                            onChange={(size) => updateQuestion(index, 'questionFontSize', size)}
                                            withSlider
                                            label={__('Question Font Size', 'blockxpert')}
                                        />
                                        <TextareaControl
                                            label={__('Answer', 'blockxpert')}
                                            value={question.answer}
                                            onChange={(value) => updateQuestion(index, 'answer', value)}
                                            placeholder={__('Enter your answer...', 'blockxpert')}
                                            rows={4}
                                        />
                                        <PanelColorSettings
                                            title={__('Answer Color', 'blockxpert')}
                                            colorSettings={[{
                                                value: question.answerColor,
                                                onChange: (color) => updateQuestion(index, 'answerColor', color),
                                                label: __('Answer Color', 'blockxpert'),
                                            }]}
                                        />
                                        <FontSizePicker
                                            fontSizes={FONT_SIZES}
                                            value={question.answerFontSize}
                                            onChange={(size) => updateQuestion(index, 'answerFontSize', size)}
                                            withSlider
                                            label={__('Answer Font Size', 'blockxpert')}
                                        />
                                        <div className="faq-edit-actions">
                                            <Button
                                                isPrimary
                                                onClick={() => setEditingQuestion(null)}
                                            >
                                                {__('Save', 'blockxpert')}
                                            </Button>
                                            <Button
                                                isDestructive
                                                onClick={() => {
                                                    setEditingQuestion(null);
                                                    deleteQuestion(index);
                                                }}
                                            >
                                                {__('Delete', 'blockxpert')}
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="faq-question-content">
                                        <div 
                                            className="faq-question-header"
                                            onClick={() => toggleQuestion(index)}
                                        >
                                            <h3 className="faq-question-text" style={{
                                                ...questionStyle,
                                                color: question.questionColor || questionStyle.color,
                                                fontSize: question.questionFontSize || questionStyle.fontSize
                                            }}>{question.question || __('Untitled Question', 'blockxpert')}</h3>
                                            <div className="faq-question-actions">
                                                <Button
                                                    isSmall
                                                    variant="secondary"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        setEditingQuestion(question.id);
                                                    }}
                                                >
                                                    {__('Edit', 'blockxpert')}
                                                </Button>
                                                <span 
                                                    className={`faq-toggle-icon ${expandedQuestions.has(index) ? 'open' : ''}`}
                                                >
                                                    +
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div 
                                            className={`faq-answer ${expandedQuestions.has(index) ? 'is-open' : ''}`}
                                            style={{
                                                transitionDuration: `${animationDuration}ms`,
                                                maxHeight: expandedQuestions.has(index) ? '500px' : '0'
                                            }}
                                        >
                                            <p style={{
                                                ...answerStyle,
                                                color: question.answerColor || answerStyle.color,
                                                fontSize: question.answerFontSize || answerStyle.fontSize
                                            }}>{question.answer || __('No answer provided.', 'blockxpert')}</p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                        
                        <div className="faq-actions">
                            <Button
                                variant="primary"
                                onClick={addQuestion}
                            >
                                {__('Add Question', 'blockxpert')}
                            </Button>
                            
                            {aiEnabled && (
                                <Button
                                    variant="secondary"
                                    onClick={generateAIQuestions}
                                    isBusy={loading}
                                    disabled={!apiKey}
                                >
                                    {__('Generate More AI Questions', 'blockxpert')}
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
} 