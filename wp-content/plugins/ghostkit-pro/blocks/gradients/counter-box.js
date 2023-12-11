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
  if (props.props.name === 'ghostkit/counter-box') {
    if (props.attribute === 'numberColor' || props.attribute === 'hoverNumberColor') {
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
  if (props.name === 'ghostkit/counter-box') {
    let numberGradient = styles['--gkt-counter-box--number__color'];
    let hoverNumberGradient =
      styles['&:hover'] && styles['&:hover']['--gkt-counter-box--number__color'];

    numberGradient = isGradient(numberGradient) ? numberGradient : false;
    hoverNumberGradient = isGradient(hoverNumberGradient) ? hoverNumberGradient : false;

    if (numberGradient || hoverNumberGradient) {
      styles['&:hover'] = styles['&:hover'] || {};
    }

    if (numberGradient) {
      // text gradient.
      styles['--gkt-pro-counter-box--number__image'] = numberGradient;
      styles['&:hover']['--gkt-pro-counter-box--number__image'] = 'none';

      // color fallback.
      styles['--gkt-counter-box--number__color'] = fallbackColor(numberGradient);
    }
    if (hoverNumberGradient) {
      // text gradient.
      styles['&:hover']['--gkt-pro-counter-box--number__image'] = hoverNumberGradient;
      styles['&:hover']['--gkt-counter-box--number__color'] = fallbackColor(hoverNumberGradient);
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
  if (props.name === 'ghostkit/counter-box') {
    if (
      (props.attributes.numberColor && isGradient(props.attributes.numberColor)) ||
      (props.attributes.hoverNumberColor && isGradient(props.attributes.hoverNumberColor))
    ) {
      className = classnames(className, 'ghostkit-pro-text-gradient');
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
