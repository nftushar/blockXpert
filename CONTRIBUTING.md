# Contributing to BlockXpert

Thanks for contributing! Follow these guidelines when adding or modifying blocks.

## Adding a new block

1. Create a folder under `src/blocks/<block-name>/`.
2. Add an `index.js` that registers the block and imports styles and editor components.
3. Add `edit.js`, `view.js`, `style.scss`, `editor.css` as needed.
4. Add `block.json` at the block root that references build files, e.g.:
   ```json
   {
     "name": "blockxpert/<block-name>",
     "editorScript": "file:../../build/<block-name>/index.js",
     "editorStyle": "file:../../build/<block-name>/editor.css",
     "style": "file:../../build/<block-name>/style-index.css"
   }
   ```
5. The build system auto-discovers block assets, so running `npm run build:assets` will produce `build/<block-name>/` files.

## Testing

- Use `npm run dev` to start a watch mode and preview changes in the block editor.
- Add automated tests where applicable and open a PR for review.

## Notes

- Keep editor and frontend styles separated (`editor.scss` vs `style.scss`) to avoid conflicts.
- Avoid duplicate block slugs — search existing blocks before naming.
