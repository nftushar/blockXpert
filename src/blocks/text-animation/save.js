import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
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

  const blockProps = useBlockProps.save({
    style: {
      minHeight: `${minHeight}px`,
      display: 'flex',
      alignItems: 'center',
      justifyContent: alignment === 'center' ? 'center' : alignment === 'left' ? 'flex-start' : 'flex-end',
      flexDirection: 'column',
    },
  });

  return (
    <div {...blockProps}>
      <div
        className="blockxpert-text-animation-wrapper"
        data-animation-type={animationType}
        data-duration={duration}
        data-stagger={stagger}
        data-ease={ease}
        data-enable-blur={enableBlur}
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
            data-headline-color={headlineColor}
            data-anim-color={headlineAnimColor}
            style={{
              color: headlineColor,
              fontSize: 'clamp(2.5rem, 8vw, 6rem)',
              fontWeight: 800,
              lineHeight: 1.3,
              letterSpacing: '-1px',
              margin: '0 0 20px 0',
            }}
          >
            {headline}
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
            {tagline}
          </p>
        </div>
      </div>
    </div>
  );
}
