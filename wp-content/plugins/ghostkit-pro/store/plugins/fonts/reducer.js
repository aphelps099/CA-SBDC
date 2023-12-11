/**
 * External dependencies
 */
const { merge } = window.lodash;

function reducer(state = { data: false }, action = {}) {
  switch (action.type) {
    case 'SET_CUSTOM_FONTS':
      if (action.data) {
        if (state.data) {
          const result = merge(state.data, action.data);

          // We should overwrite fonts data.
          if (action.data.adobe && action.data.adobe.fonts) {
            result.adobe.fonts = action.data.adobe.fonts;
          }
          if (action.data.custom && action.data.custom) {
            result.custom = action.data.custom;
          }

          return {
            data: result,
          };
        }
        return {
          data: action.data,
        };
      }
      break;
    // no default
  }

  return state;
}

export default reducer;
