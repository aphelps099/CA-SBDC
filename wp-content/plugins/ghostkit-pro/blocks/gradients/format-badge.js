/**
 * Internal dependencies
 */
import ColorPicker from '../_components/color-picker';

/**
 * WordPress dependencies
 */
const { addFilter } = wp.hooks;

/**
 * Override color picker control to add gradients
 *
 * @param {Object} Control JSX control.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addGradientControl(Control, props) {
  const { property, value, format, data } = props;

  if (format === 'ghostkit/badge' && property === 'background') {
    return (
      <ColorPicker
        useDropdown={false}
        value={value}
        onChange={(color) => {
          data.toggleFormat(color, !color);
        }}
      />
    );
  }

  return Control;
}

addFilter(
  'ghostkit.editor-format.controls',
  'ghostkit-pro/editor-format/badge/addGradientControl',
  addGradientControl
);
