/**
 * Internal dependencies
 */
import ResponsiveToggle from '../../../components/responsive-toggle';
import useStyles from '../../../hooks/use-styles';
import useResponsive from '../../../hooks/use-responsive';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { addFilter } = wp.hooks;

const {
  SelectControl,
  ToolsPanelItem: __stableToolsPanelItem,
  __experimentalToolsPanelItem,
} = wp.components;

const ToolsPanelItem = __stableToolsPanelItem || __experimentalToolsPanelItem;

const { hasBlockSupport } = wp.blocks;

function CustomCSSUserSelectTools(props) {
  const { getStyle, hasStyle, setStyles, resetStyles } = useStyles(props);

  const { device, allDevices } = useResponsive();

  let hasUserSelect = false;

  ['', ...Object.keys(allDevices)].forEach((thisDevice) => {
    hasUserSelect = hasUserSelect || hasStyle('user-select', thisDevice);
  });

  return (
    <ToolsPanelItem
      label={__('User Select', 'ghostkit')}
      hasValue={() => !!hasUserSelect}
      onSelect={() => {
        if (!hasStyle('user-select')) {
          setStyles({ 'user-select': 'none' });
        }
      }}
      onDeselect={() => {
        resetStyles(['user-select'], true);
      }}
      isShownByDefault={false}
    >
      <SelectControl
        label={
          <>
            {__('User Select', 'ghostkit')}
            <ResponsiveToggle
              checkActive={(checkMedia) => {
                return hasStyle('user-select', checkMedia);
              }}
            />
          </>
        }
        value={getStyle('user-select', device)}
        onChange={(val) => {
          setStyles({ 'user-select': val }, device);
        }}
        options={[
          {
            value: 'none',
            label: __('None', 'ghostkit'),
          },
          {
            value: 'auto',
            label: __('Auto', 'ghostkit'),
          },
        ]}
      />
    </ToolsPanelItem>
  );
}

addFilter(
  'ghostkit.extension.customCSS.tools',
  'ghostkit/extension/customCSS/tools/userSelect',
  (children, { props }) => {
    const hasUserSelectSupport = hasBlockSupport(props.name, [
      'ghostkit',
      'customCSS',
      'userSelect',
    ]);

    if (!hasUserSelectSupport) {
      return children;
    }

    return (
      <>
        {children}
        <CustomCSSUserSelectTools {...props} />
      </>
    );
  }
);
