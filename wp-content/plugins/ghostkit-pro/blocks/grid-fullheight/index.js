/* eslint-disable no-param-reassign */
/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * WordPress dependencies
 */
const { createHigherOrderComponent } = wp.compose;

const { addFilter } = wp.hooks;

const { Fragment, Component } = wp.element;

const { __ } = wp.i18n;

const { ToolbarGroup, Button } = wp.components;

const { BlockControls } = wp.blockEditor;

const { withSelect } = wp.data;

const { GHOSTKIT } = window;

/**
 * Filters registered block settings, extending attributes to include `fullheight`.
 *
 * @param  {Object} settings Original block settings
 * @return {Object}          Filtered block settings
 */
export function addAttribute(settings) {
  if ('ghostkit/grid' === settings.name) {
    settings.ghostkit = settings.ghostkit || {};
    settings.ghostkit.supports = settings.ghostkit.supports || {};
    settings.ghostkit.supports.rowFullHeight = true;
  }

  let allow = false;

  if (GHOSTKIT.hasBlockSupport(settings, 'rowFullHeight', false)) {
    allow = true;
  }

  if (allow) {
    settings.attributes.fullheight = {
      type: 'boolean',
      default: false,
    };
  }

  return settings;
}

/**
 * Component Class
 */
class GridFullHeightControl extends Component {
  render() {
    const { attributes, setAttributes, getIcon } = this.props;
    const { columns, fullheight } = attributes;

    if (0 >= columns) {
      return null;
    }

    return (
      <BlockControls>
        <ToolbarGroup>
          <Button
            icon={getIcon('icon-fullheight')}
            title={__('FullHeight', 'ghostkit-pro')}
            onClick={() => setAttributes({ fullheight: !fullheight })}
            isPressed={fullheight}
          />
        </ToolbarGroup>
      </BlockControls>
    );
  }
}

const GridFullHeightControlWithSelect = withSelect((select) => {
  const { getIcon } = select('ghostkit/base/utils').get();

  return {
    getIcon,
  };
})(GridFullHeightControl);

/**
 * Override the default edit UI to include new toolbar controls for block fullheight.
 *
 * @param  {Function} BlockEdit Original component
 * @return {Function}           Wrapped component
 */
const addGridFullHeight = createHigherOrderComponent(
  (BlockEdit) =>
    function (props) {
      const { name } = props;

      if ('ghostkit/grid' === name) {
        return (
          <Fragment>
            <GridFullHeightControlWithSelect {...props} />
            <BlockEdit {...props} />
          </Fragment>
        );
      }

      return <BlockEdit {...props} />;
    },
  'addGridFullHeight'
);

/**
 * Classnames filter.
 *
 * @param {String} className Classname applied to save and edit element.
 * @param {Object} props Block props.
 *
 * @return {String} Classname.
 */
function customClassName(className, props) {
  if (GHOSTKIT.hasBlockSupport(props.name, 'rowFullHeight', false) && props.attributes.fullheight) {
    className = classnames(className, 'ghostkit-pro-grid-fullheight');
  }

  return className;
}

addFilter('blocks.registerBlockType', 'ghostkit-pro/grid/fullheight/addAttribute', addAttribute);
addFilter('editor.BlockEdit', 'ghostkit-pro/grid/fullheight/addControl', addGridFullHeight);
addFilter('ghostkit.blocks.className', 'ghostkit-pro/grid/fullheight/className', customClassName);
addFilter('ghostkit.editor.className', 'ghostkit-pro/grid/fullheight/className', customClassName);
