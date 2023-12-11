/* eslint-disable no-param-reassign */
/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import { isGradient, fallbackColor } from '../_utils/gradient-parser';
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
  if (props.props.name === 'ghostkit/button-single') {
    if (props.attribute === 'color' || props.attribute === 'hoverColor') {
      return <ColorPicker {...Control.props} key={props.props.name + props.attribute} />;
    }
  }

  return Control;
}

/**
 * Override block styles and add gradients if need
 *
 * @param {Array} styles styles.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addGradientStyles(styles, props) {
  if (props.name === 'ghostkit/button-single') {
    // Background.
    if (
      styles['--gkt-button__background-color'] &&
      isGradient(styles['--gkt-button__background-color'])
    ) {
      styles['--gkt-pro-button__background-image'] = styles['--gkt-button__background-color'];
      styles['--gkt-button__background-color'] = fallbackColor(
        styles['--gkt-button__background-color']
      );
    }

    // Hover Background.
    if (
      styles['--gkt-button-hover__background-color'] &&
      isGradient(styles['--gkt-button-hover__background-color'])
    ) {
      styles['--gkt-pro-button-hover__background-image'] =
        styles['--gkt-button-hover__background-color'];
      styles['--gkt-button-hover__background-color'] = fallbackColor(
        styles['--gkt-button-hover__background-color']
      );
      styles['--gkt-button-focus__background-color'] = fallbackColor(
        styles['--gkt-button-focus__background-color']
      );
    }
  }

  return styles;
}

/**
 * Classnames filter.
 *
 * @param {String} className Classname applied to save and edit element.
 * @param {Object} props Block props.
 *
 * @return {String} Classname.
 */
function customClassName(className, props) {
  if (props.name === 'ghostkit/button-single') {
    if (
      (props.attributes.color && isGradient(props.attributes.color)) ||
      (props.attributes.hoverColor && isGradient(props.attributes.hoverColor))
    ) {
      className = classnames(className, 'ghostkit-pro-bg-gradient');
    }
  }

  return className;
}

addFilter('ghostkit.editor.controls', 'ghostkit-pro/editor/addGradientControl', addGradientControl);
addFilter(
  'ghostkit.blocks.customStyles',
  'ghostkit-pro/button/addGradientStyles',
  addGradientStyles
);
addFilter('ghostkit.blocks.className', 'ghostkit-pro/gradient/className', customClassName);
addFilter('ghostkit.editor.className', 'ghostkit-pro/gradient/className', customClassName);
