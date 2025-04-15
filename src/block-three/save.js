export default function save({ attributes }) {
    return (
      <div className="wp-block-gutenberg-blocks-block-two">
        <h2>{attributes.title}</h2>
        <p>{attributes.content}</p>
      </div>
    );
  }