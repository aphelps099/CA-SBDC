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
  if (props.props.name === 'ghostkit/progress') {
    if (
      props.attribute === 'color' ||
      props.attribute === 'hoverColor' ||
      props.attribute === 'backgroundColor' ||
      props.attribute === 'hoverBackgroundColor'
    ) {
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
  if (props.name === 'ghostkit/progress') {
    const barColor = styles['--gkt-progress--bar__background-color'];
    const bgColor = styles['--gkt-progress__background-color'];
    const hoverBarColor =
      styles['&:hover'] && styles['&:hover']['--gkt-progress--bar__background-color'];
    const hoverBgColor = styles['&:hover'] && styles['&:hover']['--gkt-progress__background-color'];

    let barGradient = isGradient(barColor) ? barColor : false;
    let bgGradient = isGradient(bgColor) ? bgColor : false;
    let hoverBarGradient = isGradient(hoverBarColor) ? hoverBarColor : false;
    let hoverBgGradient = isGradient(hoverBgColor) ? hoverBgColor : false;

    // Convert solid color to gradient.
    if (!barGradient && barColor) {
      barGradient = `linear-gradient(${barColor} 0%, ${barColor} 100%)`;
    }
    if (!bgGradient && bgColor) {
      bgGradient = `linear-gradient(${bgColor} 0%, ${bgColor} 100%)`;
    }
    if (!hoverBarGradient && hoverBarColor) {
      hoverBarGradient = `linear-gradient(${hoverBarColor} 0%, ${hoverBarColor} 100%)`;
    }
    if (!hoverBgGradient && hoverBgColor) {
      hoverBgGradient = `linear-gradient(${hoverBgColor} 0%, ${hoverBgColor} 100%)`;
    }

    // Bar Gradient.
    if (barGradient) {
      styles['--gkt-pro-progress--bar__background-image'] = barGradient;

      // color fallback.
      styles['--gkt-progress--bar__background-color'] = fallbackColor(barGradient);
    }
    if (hoverBarGradient) {
      styles['--gkt-pro-progress--bar-hover__background-image'] = hoverBarGradient;

      // color fallback.
      styles['&:hover']['--gkt-progress--bar__background-color'] = fallbackColor(hoverBarGradient);
    }

    // Progress Gradient.
    if (bgGradient) {
      styles['--gkt-pro-progress__background-image'] = bgGradient;

      // color fallback.
      styles['--gkt-progress__background-color'] = fallbackColor(bgGradient);
    }
    if (hoverBgGradient) {
      styles['--gkt-pro-progress-hover__background-image'] = hoverBgGradient;

      // color fallback.
      styles['&:hover']['--gkt-progress__background-color'] = fallbackColor(hoverBgGradient);
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
  if (props.name === 'ghostkit/progress') {
    if (
      (props.attributes.color && isGradient(props.attributes.color)) ||
      (props.attributes.hoverColor && isGradient(props.attributes.hoverColor)) ||
      (props.attributes.backgroundColor && isGradient(props.attributes.backgroundColor)) ||
      (props.attributes.hoverBackgroundColor && isGradient(props.attributes.hoverBackgroundColor))
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
