/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Returns the ready to use className for grid column.
 *
 * @param {object} attributes - block attributes.
 *
 * @return {String} Classname for Grid container.
 */
export default function colClass(attributes) {
  let result = 'ghostkit-col';

  Object.keys(attributes).forEach((key) => {
    if (attributes[key]) {
      let prefix = key.split('_')[0];
      let type = key.split('_')[1];

      if (!type) {
        type = prefix;
        prefix = '';
      }

      if (type && (type === 'size' || type === 'order' || type === 'verticalAlign')) {
        prefix = prefix ? `-${prefix}` : '';

        switch (type) {
          case 'size':
            type = '';
            break;
          case 'order':
            type = `-${type}`;
            break;
          case 'verticalAlign':
            type = '-align-self';
            break;
          // no default
        }

        result = classnames(
          result,
          `ghostkit-col${type}${prefix || ''}${
            attributes[key] !== 'auto' ? `-${attributes[key]}` : ''
          }`
        );
      }
    }
  });

  return result;
}
