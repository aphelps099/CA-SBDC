/**
 * WordPress dependencies
 */
/**
 * Internal dependencies
 */
import GridResizeControl from './resize-control';
import InfoColControl from './info-col-control';

const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { PanelBody } = wp.components;

/**
 * Add grid visual editor
 *
 * @param {Object} Control JSX control.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addGridVisualEditor(Control, props) {
  if (props.props.name === 'ghostkit/grid' && props.attribute === 'columns') {
    return (
      <Fragment>
        {Control}
        <PanelBody>
          <GridResizeControl {...props.props} />
        </PanelBody>
      </Fragment>
    );
  }

  return Control;
}

/**
 * Remove columns settings
 *
 * @param {Object} Control JSX control.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function removeColumnSettings(Control, props) {
  if (props.props.name === 'ghostkit/grid-column' && props.attribute === 'columnSettings') {
    return <InfoColControl {...props.props} />;
  }

  return Control;
}

addFilter(
  'ghostkit.editor.controls',
  'ghostkit-pro/editor/addGridVisualEditor',
  addGridVisualEditor
);
addFilter(
  'ghostkit.editor.controls',
  'ghostkit-pro/editor/removeColumnSettings',
  removeColumnSettings
);
