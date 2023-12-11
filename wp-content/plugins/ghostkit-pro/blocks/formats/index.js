/**
 * Internal dependencies
 */
import * as animatedText from './animated-text';
import * as tooltip from './tooltip';

/**
 * WordPress dependencies
 */
const { registerFormatType } = wp.richText;

/**
 * Register formats
 */
[animatedText, tooltip].forEach(({ name, settings }) => {
  registerFormatType(name, settings);
});
