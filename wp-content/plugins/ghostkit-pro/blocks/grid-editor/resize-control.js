/* eslint-disable indent */
/* eslint-disable no-param-reassign */
/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import getColClass from './get-col-class';

/**
 * WordPress dependencies
 */
const { jQuery: $ } = window;

const { Component } = wp.element;

const { __, sprintf } = wp.i18n;

const { Button, Dropdown, SelectControl, ResizableBox } = wp.components;

const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;

const { Fragment } = wp.element;

/**
 * Get array for Select element.
 *
 * @returns {Array} array for Select.
 */
const getDefaultColumnSizes = function () {
  const result = [
    {
      label: __('Inherit from larger'),
      value: '',
    },
    {
      label: __('Auto'),
      value: 'auto',
    },
    {
      label: __('Grow', 'ghostkit-pro'),
      value: 'grow',
    },
  ];

  for (let k = 1; k <= 12; k += 1) {
    result.push({
      label: sprintf(
        k === 1 ? __('%d Column (%s)') : __('%d Columns (%s)'),
        k,
        `${Math.round(100 * (k / 12) * 100) / 100}%`
      ),
      value: k,
    });
  }
  return result;
};

/**
 * Get array for Select element.
 *
 * @param {Number} columns - number of available columns.
 *
 * @returns {Array} array for Select.
 */
const getDefaultColumnOrders = function (columns = 12) {
  const result = [
    {
      label: __('Inherit from larger'),
      value: '',
    },
    {
      label: __('Auto'),
      value: 'auto',
    },
    {
      label: __('First'),
      value: 'first',
    },
  ];

  for (let k = 1; k <= columns; k += 1) {
    result.push({
      label: k,
      value: k,
    });
  }

  result.push({
    label: __('Last'),
    value: 'last',
  });

  return result;
};

function Item(props) {
  const { $this, idx, colData, mediaName, sizeName, orderName, verticalAlignName } = props;

  const { getIcon, ToggleGroup } = $this.props;

  const colClass = {
    size: colData.attributes[sizeName],
    order: colData.attributes[orderName],
    verticalAlign: colData.attributes[verticalAlignName],
  };

  // get inherited value
  if (!$this.isChanged(mediaName, 'size')) {
    colClass.size = colData.attributes[$this.getInheritedAttributeNames(mediaName).size];
  }
  if (!$this.isChanged(mediaName, 'order')) {
    colClass.order = colData.attributes[$this.getInheritedAttributeNames(mediaName).order];
  }
  if (!$this.isChanged(mediaName, 'verticalAlign')) {
    colClass.verticalAlign =
      colData.attributes[$this.getInheritedAttributeNames(mediaName).verticalAlign];
  }

  return (
    <ResizableBox
      key={colData.clientId}
      className={getColClass(colClass)}
      enable={{
        right: true,
      }}
      minWidth={`${100 / 12}%`}
      maxWidth="100%"
      onResizeStart={(event, direction, elt) => {
        elt.style.transition = 'none';
      }}
      onResize={(event, direction, elt) => {
        elt.style.flex = `0 0 ${elt.style.width}`;
      }}
      onResizeStop={(event, direction, elt) => {
        // calculate column size.
        const newColSize = Math.round(
          12 * (parseInt(elt.style.width, 10) / $(elt).parent().width())
        );
        elt.style.flex = '';
        elt.style.width = '';
        elt.style.transition = '';

        if ($this.isChanged(mediaName) && newColSize) {
          $this.props.updateBlockAttributes(colData.clientId, {
            [sizeName]: `${newColSize}`,
          });
        }
      }}
    >
      <Dropdown
        className="ghostkit-pro-grid-resize-control-col-wrap"
        position="top center"
        renderToggle={({ isOpen, onToggle }) => (
          <Fragment>
            <Button
              className="ghostkit-pro-grid-resize-control-col-settings-handler"
              onClick={onToggle}
              aria-expanded={isOpen}
            >
              <svg
                className="ghostkit-svg-icon ghostkit-svg-icon-fa"
                aria-hidden="true"
                role="img"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 512 512"
              >
                <path
                  fill="currentColor"
                  d="M487.4 315.7l-42.6-24.6c4.3-23.2 4.3-47 0-70.2l42.6-24.6c4.9-2.8 7.1-8.6 5.5-14-11.1-35.6-30-67.8-54.7-94.6-3.8-4.1-10-5.1-14.8-2.3L380.8 110c-17.9-15.4-38.5-27.3-60.8-35.1V25.8c0-5.6-3.9-10.5-9.4-11.7-36.7-8.2-74.3-7.8-109.2 0-5.5 1.2-9.4 6.1-9.4 11.7V75c-22.2 7.9-42.8 19.8-60.8 35.1L88.7 85.5c-4.9-2.8-11-1.9-14.8 2.3-24.7 26.7-43.6 58.9-54.7 94.6-1.7 5.4.6 11.2 5.5 14L67.3 221c-4.3 23.2-4.3 47 0 70.2l-42.6 24.6c-4.9 2.8-7.1 8.6-5.5 14 11.1 35.6 30 67.8 54.7 94.6 3.8 4.1 10 5.1 14.8 2.3l42.6-24.6c17.9 15.4 38.5 27.3 60.8 35.1v49.2c0 5.6 3.9 10.5 9.4 11.7 36.7 8.2 74.3 7.8 109.2 0 5.5-1.2 9.4-6.1 9.4-11.7v-49.2c22.2-7.9 42.8-19.8 60.8-35.1l42.6 24.6c4.9 2.8 11 1.9 14.8-2.3 24.7-26.7 43.6-58.9 54.7-94.6 1.5-5.5-.7-11.3-5.6-14.1zM256 336c-44.1 0-80-35.9-80-80s35.9-80 80-80 80 35.9 80 80-35.9 80-80 80z"
                />
              </svg>
            </Button>
            <div className="ghostkit-pro-grid-resize-control-col-number">{idx + 1}</div>
          </Fragment>
        )}
        renderContent={() => (
          <div className="ghostkit-pro-grid-resize-control-col-settings">
            <SelectControl
              label={__('Size')}
              value={colData.attributes[sizeName]}
              onChange={(value) => {
                $this.props.updateBlockAttributes(colData.clientId, {
                  [sizeName]: value,
                });
              }}
              options={getDefaultColumnSizes()}
            />
            <SelectControl
              label={__('Order')}
              value={colData.attributes[orderName]}
              onChange={(value) => {
                $this.props.updateBlockAttributes(colData.clientId, {
                  [orderName]: value,
                });
              }}
              options={getDefaultColumnOrders()}
            />
            <ToggleGroup
              label={__('Vertical alignment', 'ghostkit-pro')}
              value={colData.attributes[verticalAlignName]}
              options={[
                {
                  label: getIcon('icon-vertical-top'),
                  value: 'start',
                },
                {
                  label: getIcon('icon-vertical-center'),
                  value: 'center',
                },
                {
                  label: getIcon('icon-vertical-bottom'),
                  value: 'end',
                },
              ]}
              onChange={(value) => {
                $this.props.updateBlockAttributes(colData.clientId, {
                  [verticalAlignName]: value,
                });
              }}
              allowReset
            />
          </div>
        )}
      />
    </ResizableBox>
  );
}

function ItemsList(props) {
  const { $this, mediaName, sizeName, orderName, verticalAlignName } = props;

  const { attributes } = $this.props;

  const gridClassName = classnames(
    'ghostkit-grid ghostkit-pro-grid-resize-control',
    `ghostkit-grid-gap-${attributes.gap}`,
    attributes.verticalAlign ? `ghostkit-grid-align-items-${attributes.verticalAlign}` : false,
    attributes.horizontalAlign
      ? `ghostkit-grid-justify-content-${attributes.horizontalAlign}`
      : false
  );

  return (
    <div className={gridClassName}>
      {!$this.isChanged(mediaName) ? (
        <div className="ghostkit-pro-grid-resize-control-inherited">
          <div>{__('Inherits from larger screen size')}</div>
          <Button
            isSmall
            isSecondary
            onClick={() => {
              const newSizeName = $this.getInheritedAttributeNames(mediaName).size;
              const newOrderName = $this.getInheritedAttributeNames(mediaName).order;
              const newVerticalAlignName =
                $this.getInheritedAttributeNames(mediaName).verticalAlign;

              $this.props.block.innerBlocks.forEach((colData) => {
                $this.props.updateBlockAttributes(colData.clientId, {
                  [sizeName]: colData.attributes[newSizeName],
                  [orderName]: colData.attributes[newOrderName],
                  [verticalAlignName]: colData.attributes[newVerticalAlignName],
                });
              });
            }}
          >
            {__('Edit')}
          </Button>
        </div>
      ) : (
        ''
      )}
      {$this.props.block.innerBlocks.map((colData, i) => (
        <Item
          // eslint-disable-next-line react/no-array-index-key
          key={`item-${i}`}
          idx={i}
          colData={colData}
          mediaName={mediaName}
          sizeName={sizeName}
          orderName={orderName}
          verticalAlignName={verticalAlignName}
          $this={$this}
        />
      ))}
      {$this.isChanged(mediaName) ? (
        <div className="ghostkit-pro-grid-resize-control-workspace ghostkit-pro-grid-resize-control-workspace-active">
          {/* eslint-disable-next-line react/no-array-index-key */}
          {Array.apply(0, Array(12)).map((x, i) => (
            // eslint-disable-next-line react/no-array-index-key
            <div className="ghostkit-col ghostkit-col-grow" key={`col-${i}`}>
              <div />
            </div>
          ))}
        </div>
      ) : (
        ''
      )}
    </div>
  );
}

class GridResizeControl extends Component {
  constructor(...args) {
    super(...args);

    this.isChanged = this.isChanged.bind(this);
    this.getParentMedia = this.getParentMedia.bind(this);
    this.getInheritedAttributeNames = this.getInheritedAttributeNames.bind(this);
  }

  // eslint-disable-next-line class-methods-use-this
  getParentMedia(currentMedia) {
    switch (currentMedia) {
      case 'sm':
        return 'md';
      case 'md':
        return 'lg';
      case 'lg':
        return 'xl';
      default:
        return 'all';
    }
  }

  getInheritedAttributeNames(mediaName) {
    let parentMediaSize = '';
    let parentMediaSizeChanged = false;
    let newSizeName = 'size';
    while (!parentMediaSizeChanged && parentMediaSize !== 'all') {
      parentMediaSize = this.getParentMedia(parentMediaSize || mediaName);
      parentMediaSizeChanged = this.isChanged(parentMediaSize, newSizeName);
    }
    if (parentMediaSize !== 'all') {
      newSizeName = `${parentMediaSize}_${newSizeName}`;
    }

    let parentMediaOrder = '';
    let parentMediaOrderChanged = false;
    let newOrderName = 'order';
    while (!parentMediaOrderChanged && parentMediaOrder !== 'all') {
      parentMediaOrder = this.getParentMedia(parentMediaOrder || mediaName);
      parentMediaOrderChanged = this.isChanged(parentMediaOrder, newOrderName);
    }
    if (parentMediaOrder !== 'all') {
      newOrderName = `${parentMediaOrder}_${newOrderName}`;
    }

    let parentMediaVerticalAlign = '';
    let parentMediaVerticalAlignChanged = false;
    let newVerticalAlignName = 'verticalAlign';
    while (!parentMediaVerticalAlignChanged && parentMediaVerticalAlign !== 'all') {
      parentMediaVerticalAlign = this.getParentMedia(parentMediaVerticalAlign || mediaName);
      parentMediaVerticalAlignChanged = this.isChanged(
        parentMediaVerticalAlign,
        newVerticalAlignName
      );
    }
    if (parentMediaVerticalAlign !== 'all') {
      newVerticalAlignName = `${parentMediaVerticalAlign}_${newVerticalAlignName}`;
    }

    return {
      size: newSizeName,
      order: newOrderName,
      verticalAlign: newVerticalAlignName,
    };
  }

  isChanged(mediaName, attrName = false) {
    let isChanged = mediaName === 'all';

    if (this.props.block && this.props.block.innerBlocks && this.props.block.innerBlocks.length) {
      this.props.block.innerBlocks.forEach((colData) => {
        if (attrName) {
          isChanged = isChanged || !!colData.attributes[`${mediaName}_${attrName}`];
        } else {
          isChanged =
            isChanged ||
            !!colData.attributes[`${mediaName}_size`] ||
            !!colData.attributes[`${mediaName}_order`] ||
            !!colData.attributes[`${mediaName}_verticalAlign`];
        }
      });
    }

    return isChanged;
  }

  render() {
    const { ResponsiveTabPanel } = this.props;

    const filledTabs = {};
    if (
      window.ghostkitVariables &&
      window.ghostkitVariables.media_sizes &&
      Object.keys(window.ghostkitVariables.media_sizes).length
    ) {
      Object.keys(window.ghostkitVariables.media_sizes).forEach((media) => {
        filledTabs[media] = this.isChanged(media);
      });
    }

    return (
      <ResponsiveTabPanel filledTabs={filledTabs}>
        {(tabData) => {
          let sizeName = 'size';
          let orderName = 'order';
          let verticalAlignName = 'verticalAlign';
          const mediaName = tabData.name;

          if (mediaName !== 'all') {
            sizeName = `${mediaName}_${sizeName}`;
            orderName = `${mediaName}_${orderName}`;
            verticalAlignName = `${mediaName}_${verticalAlignName}`;
          }

          let note = '';
          if (
            !this.props.block ||
            !this.props.block.innerBlocks ||
            !this.props.block.innerBlocks.length
          ) {
            note = __('Select one of default layouts to unlock columns editor.');
          }

          return (
            <Fragment>
              {this.props.block &&
              this.props.block.innerBlocks &&
              this.props.block.innerBlocks.length ? (
                <ItemsList
                  $this={this}
                  mediaName={mediaName}
                  sizeName={sizeName}
                  orderName={orderName}
                  verticalAlignName={verticalAlignName}
                />
              ) : (
                ''
              )}
              {this.isChanged(mediaName) && mediaName !== 'all' ? (
                <Fragment>
                  <Button
                    isSmall
                    isSecondary
                    className="ghostkit-pro-grid-resize-control-reset"
                    onClick={() => {
                      this.props.block.innerBlocks.forEach((colData) => {
                        this.props.updateBlockAttributes(colData.clientId, {
                          [sizeName]: '',
                          [orderName]: '',
                          [verticalAlignName]: '',
                        });
                      });
                    }}
                  >
                    {__('Reset')}
                  </Button>
                  <div className="clear" />
                </Fragment>
              ) : (
                ''
              )}
              {note ? (
                <p>
                  <em>{note}</em>
                </p>
              ) : (
                ''
              )}
            </Fragment>
          );
        }}
      </ResponsiveTabPanel>
    );
  }
}

export default compose([
  withSelect((select, ownProps) => {
    const { getBlock } = select('core/block-editor');

    const { getIcon } = select('ghostkit/base/utils').get();

    const { ResponsiveTabPanel, ToggleGroup } = select('ghostkit/base/components').get();

    return {
      block: getBlock(ownProps.clientId),
      getIcon,
      ResponsiveTabPanel,
      ToggleGroup,
    };
  }),
  withDispatch((dispatch) => {
    const { updateBlockAttributes } = dispatch('core/block-editor');

    return {
      updateBlockAttributes,
    };
  }),
])(GridResizeControl);
