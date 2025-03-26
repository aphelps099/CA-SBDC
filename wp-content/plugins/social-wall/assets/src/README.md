# Social Wall: Feed Editor

## Contents
- [Styling Dependencies](#styling-dependencies)
- [Folder Structure](#folder-structure)
- [Feed Editor](#feed-editor)
  - [SVG Icon](#svg-icon)
  - [Feed Editor Builder JSON](#feed-editor-builder-json)

## Styling Dependencies
This app has the styling dependency of [TailwindCSS](https://tailwindcss.com/docs/installation)

## Folder Structure
```
src/
  ├─ components/  --- holds app wise components
    ├─ lib/       --- holds the generic components for SB Design system
  ├─ context/     --- react context API
  ├─ pages/       --- holds all pages for the app
  ├─ styles/      --- holds app wise scss/css files
  ├─ utils/       --- holds app wise util functions/
  index.js        --- app entry point
  SocialWall.js   --- app layout & routing
```

## Feed Editor

### SVG Icon
- Add SVG icons in [ICONS.js](https://github.com/awesomemotive/social-wall/blob/trunk/assets/src/components/FeedEditor/ICONS.js)

```javascript
import { ReactComponent as TestIcon } from '../../../images/feed-editor/test.svg';

const ICONS = {
  // ...
  test: <TestIcon />,
};

export default ICONS;
```


### Feed Editor Builder JSON
- `tab` component is the parent component.

```javascript
const tabs = [
  {
    id: 'tab-customize',
    label: 'Customize',
    items: [],
  },
  {
    id: 'tab-settings',
    label: 'Settings',
    items: [],
  }
];
```
- Add other components in `items` array

```javascript
const tabs = [
  {
    id: 'tab-customize',
    label: 'Customize',
    items: [
      {
          type: 'section',
          id: 'feed-layout',
          label: 'Feed Layout',
          items: []
      }
    ],
  },
];
```
- Parameters of components:
  - `type`: Type of the component
  - `id`: Unique identifier
  - `label`: Label of the component
  - `items`: Children component(s)

- Nested `section` can be done by adding `section` component as a children of another section

```javascript
const tabs = [
  {
    id: 'tab-customize',
    label: 'Customize',
    items: [
      {
          type: 'section',
          id: 'feed-layout',
          label: 'Feed Layout',
          items: [
            {
              type: 'section',
              id: 'custom',
              label: 'Custom',
              items: []
            }
          ],
      }
    ],
  },
];
```

### Add Feed Editor Item
- Create a new `TabItem` component. Ex:

```javascript
const NewAwesomeItem = () => {
	return (
		<div>
			<h4 className={'some-class'}>Hello World</h4>
		</div>
	);
};

export default NewAwesomeItem;
```

- Register the component on `index.js` on [TabItem](https://github.com/awesomemotive/social-wall/blob/trunk/assets/src/components/FeedEditor/TabItem/index.js)

```javascript
// ...
import NewAwesomeItem from './NewAwesomeItem';

const TabItem = {
        //...
	'new-awesome-item': NewAwesomeItem,
};

export default TabItem;
```

- Feed Editor JSON:


```javascript
const tabs = [
  {
    id: 'tab-customize',
    label: 'Customize',
    items: [
      {
          type: 'new-awesome-item',
          id: 'new-item-1',
          label: 'New Item',
          items: [],
      }
    ],
  },
];
```

- `item` prop will be dynamically injected in the component for use.

```javascript
const NewAwesomeItem = ({ item }) => {
  return (
    <div>
      <h4 className={'some-class'}>{item.label}</h4>
    </div>
  );
};

export default NewAwesomeItem;
```


