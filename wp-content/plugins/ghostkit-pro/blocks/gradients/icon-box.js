/* eslint-disable no-param-reassign */
/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import { isGradient, fallbackColor } from '../_utils/gradient-parser';
import { SVGGradientGenerator, SVGGradientGeneratorBlock } from '../_utils/svg-gradient-generator';
import ColorPicker from '../_components/color-picker';

/**
 * WordPress dependencies
 */
const { addFilter } = wp.hooks;

const { createHigherOrderComponent } = wp.compose;

const { Fragment, RawHTML } = wp.element;

/**
 * Override color picker control to add gradients
 *
 * @param {Object} Control JSX control.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addGradientControl(Control, props) {
  if (props.props.name === 'ghostkit/icon-box') {
    if (props.attribute === 'iconColor' || props.attribute === 'hoverIconColor') {
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
  if (props.name === 'ghostkit/icon-box') {
    let iconGradient = styles['--gkt-icon-box--icon__color'];
    let hoverIconGradient = styles['&:hover'] && styles['&:hover']['--gkt-icon-box--icon__color'];

    iconGradient = isGradient(iconGradient) ? iconGradient : false;
    hoverIconGradient = isGradient(hoverIconGradient) ? hoverIconGradient : false;

    if (iconGradient || hoverIconGradient) {
      styles['&:hover'] = styles['&:hover'] || {};
    }

    if (iconGradient) {
      // text gradient.
      styles['--gkt-pro-icon-box--icon__image'] = iconGradient;
      styles['&:hover']['--gkt-pro-icon-box--icon__image'] = 'none';

      // color fallback.
      styles['--gkt-icon-box--icon__color'] = fallbackColor(iconGradient);
    }
    if (hoverIconGradient) {
      // text gradient.
      styles['&:hover']['--gkt-pro-icon-box--icon__image'] = iconGradient;
      styles['&:hover']['--gkt-icon-box--icon__color'] = fallbackColor(hoverIconGradient);
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
  if (props.name === 'ghostkit/icon-box') {
    if (
      (props.attributes.iconColor && isGradient(props.attributes.iconColor)) ||
      (props.attributes.hoverIconColor && isGradient(props.attributes.hoverIconColor))
    ) {
      className = classnames(className, 'ghostkit-pro-text-gradient');
    }
  }

  return className;
}

/**
 * Add SVG gradient for FontAwesome
 *
 * @param {Object} Element Save element.
 * @param {Object} props  Block properties.
 * @param {Object} attributes  Block attributes.
 *
 * @return {Object} Filtered props applied to save element.
 */
function addIconBoxSVG(Element, props, attributes) {
  if (props.name === 'ghostkit/icon-box') {
    const isColorGradient = attributes.iconColor && isGradient(attributes.iconColor);
    const isHoverColorGradient = attributes.hoverIconColor && isGradient(attributes.hoverIconColor);

    if (attributes.ghostkitClassname && (isColorGradient || isHoverColorGradient)) {
      return (
        <Fragment>
          {Element}
          <RawHTML>
            {SVGGradientGeneratorBlock(
              attributes.iconColor,
              `ghostkit-pro-svg-gradient-icon-box-${attributes.ghostkitId}`,
              `.${attributes.ghostkitClassname} .ghostkit-icon-box-icon`
            )}
          </RawHTML>
          <RawHTML>
            {SVGGradientGeneratorBlock(
              attributes.hoverIconColor,
              `ghostkit-pro-svg-gradient-hover-icon-box-${attributes.ghostkitId}`,
              `.${attributes.ghostkitClassname}:hover .ghostkit-icon-box-icon`
            )}
          </RawHTML>
        </Fragment>
      );
    }
  }

  return Element;
}

/**
 * Add SVG gradient for FontAwesome in Editor
 */
const addIconBoxSVGEditor = createHigherOrderComponent(
  (BlockEdit) =>
    function (props) {
      if (props.name === 'ghostkit/icon-box') {
        const isColorGradient =
          props.attributes.iconColor && isGradient(props.attributes.iconColor);
        const isHoverColorGradient =
          props.attributes.hoverIconColor && isGradient(props.attributes.hoverIconColor);

        if (props.attributes.ghostkitClassname && (isColorGradient || isHoverColorGradient)) {
          return (
            <Fragment>
              <BlockEdit {...props} />
              <RawHTML>
                {SVGGradientGenerator(
                  props.attributes.iconColor,
                  `ghostkit-pro-svg-gradient-icon-box-${props.attributes.ghostkitId}`,
                  `.${props.attributes.ghostkitClassname} .ghostkit-icon-box-icon`
                )}
              </RawHTML>
              <RawHTML>
                {SVGGradientGenerator(
                  props.attributes.hoverIconColor,
                  `ghostkit-pro-svg-gradient-hover-icon-box-${props.attributes.ghostkitId}`,
                  `.${props.attributes.ghostkitClassname}:hover .ghostkit-icon-box-icon`
                )}
              </RawHTML>
            </Fragment>
          );
        }
      }

      return <BlockEdit {...props} />;
    },
  'withSVGGradient'
);

addFilter('ghostkit.editor.controls', 'ghostkit-pro/editor/addGradientControl', addGradientControl);
addFilter(
  'ghostkit.blocks.customStyles',
  'ghostkit-pro/button/addGradientStyles',
  addGradientStyles
);
addFilter('ghostkit.blocks.className', 'ghostkit-pro/gradient/className', customClassName);
addFilter('ghostkit.editor.className', 'ghostkit-pro/gradient/className', customClassName);
addFilter('blocks.getSaveElement', 'ghostkit-pro/icon_box/gradient/svg', addIconBoxSVG);
addFilter('editor.BlockEdit', 'my-plugin/with-inspector-controls', addIconBoxSVGEditor);
