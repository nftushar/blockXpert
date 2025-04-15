export default function save({ attributes }) {
    return (
      <div className="wp-block-gutenberg-blocks-block-three">
        <h2>{attributes.title}</h2>
        <p>{attributes.content}</p>
      </div>
    );
  }