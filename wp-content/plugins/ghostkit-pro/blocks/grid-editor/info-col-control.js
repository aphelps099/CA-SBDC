/**
 * WordPress dependencies
 */
const { Component } = wp.element;

const { __ } = wp.i18n;

const { Button, PanelBody } = wp.components;

const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;

/**
 * Component Class
 */
class InfoColControl extends Component {
  constructor(...args) {
    super(...args);

    this.findParentGrid = this.findParentGrid.bind(this);
  }

  findParentGrid(rootBlock) {
    const { block } = this.props;

    let result = false;

    if (rootBlock.innerBlocks && rootBlock.innerBlocks.length) {
      rootBlock.innerBlocks.forEach((item) => {
        if (!result && item.clientId === block.clientId) {
          result = rootBlock;
        } else if (!result) {
          result = this.findParentGrid(item);
        }
      });
    }

    return result;
  }

  render() {
    const { rootBlock, selectBlock } = this.props;

    return (
      <PanelBody>
        <p>{__('To edit column size and order settings, select Grid block:')}</p>
        <Button
          isSmall
          isPrimary
          onClick={() => {
            const parentGrid = this.findParentGrid(rootBlock);
            if (parentGrid && parentGrid.clientId) {
              selectBlock(parentGrid.clientId);
            }
          }}
        >
          {__('Select Grid')}
        </Button>
      </PanelBody>
    );
  }
}

export default compose([
  withSelect((select, ownProps) => {
    const { getBlockHierarchyRootClientId, getBlock } = select('core/block-editor');

    return {
      rootBlock: ownProps.clientId
        ? getBlock(getBlockHierarchyRootClientId(ownProps.clientId))
        : null,
      block: getBlock(ownProps.clientId),
    };
  }),
  withDispatch((dispatch, { onSelect = () => {} }) => ({
    selectBlock(clientId) {
      dispatch('core/block-editor').selectBlock(clientId);
      onSelect(clientId);
    },
  })),
])(InfoColControl);
