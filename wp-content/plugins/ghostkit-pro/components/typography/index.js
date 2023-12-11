/* eslint-disable react/jsx-closing-tag-location */
/* eslint-disable react/jsx-one-expression-per-line */
/**
 * Import CSS
 */
import './style.scss';

/**
 * WordPress dependencies
 */
const { addFilter } = wp.hooks;

const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
const { GHOSTKIT } = window;

function ghostkitTypographyFontFamilySelectorInfo(Control, props) {
  const { fonts, adminUrl } = GHOSTKIT;

  if (
    props.props.fontFamilyCategory === 'adobe-fonts' &&
    (!fonts['adobe-fonts'].fonts || !fonts['adobe-fonts'].fonts.length)
  ) {
    return (
      <div className="ghostkit-typography-information-control ghostkit-typography-font-control">
        {__(
          'To use Adobe Fonts you need to add your fonts collections first here - ',
          'ghostkit-pro'
        )}
        <a
          target="_blank"
          rel="noopener noreferrer"
          href={`${adminUrl}admin.php?page=ghostkit&sub_page=fonts`}
        >{`${adminUrl}admin.php?page=ghostkit&sub_page=fonts`}</a>
      </div>
    );
  }

  if (
    props.props.fontFamilyCategory === 'custom-fonts' &&
    (!fonts['custom-fonts'].fonts || !fonts['custom-fonts'].fonts.length)
  ) {
    return (
      <div className="ghostkit-typography-information-control ghostkit-typography-font-control">
        {__('To use Custom Fonts you need to add your font files first here - ', 'ghostkit-pro')}
        <a
          target="_blank"
          rel="noopener noreferrer"
          href={`${adminUrl}admin.php?page=ghostkit&sub_page=fonts`}
        >{`${adminUrl}admin.php?page=ghostkit&sub_page=fonts`}</a>
      </div>
    );
  }

  return null;
}

function ghostkitTypographyAllowFonts(allowFlag, fontFamilyCategory) {
  const { fonts } = GHOSTKIT;

  if (fontFamilyCategory === 'adobe-fonts') {
    return fonts['adobe-fonts'].fonts && fonts['adobe-fonts'].fonts.length;
  }

  if (fontFamilyCategory === 'custom-fonts') {
    return fonts['custom-fonts'].fonts && fonts['custom-fonts'].fonts.length;
  }

  return allowFlag;
}

addFilter(
  'ghostkit.typography.fontFamilySelector.info',
  'ghostkit/components/typography',
  ghostkitTypographyFontFamilySelectorInfo
);

addFilter(
  'ghostkit.typography.allow.fonts',
  'ghostkit/components/typography',
  ghostkitTypographyAllowFonts
);
