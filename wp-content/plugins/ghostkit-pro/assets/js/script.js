import { SVGGradientGenerator } from '../../blocks/_utils/svg-gradient-generator';
import { initTooltips } from '../../blocks/formats/tooltip/api';

const { jQuery: $, Typed } = window;
const $doc = $(document);

// Init SVG gradient
$doc.on('beforeInitBlocks.ghostkit', () => {
  const $gradients = $('.ghostkit-pro-gradient-mask:not(.ghostkit-pro-gradient-mask-ready)');
  $gradients.each(function () {
    const $gradient = $(this).addClass('ghostkit-pro-gradient-mask-ready');

    $gradient.html(
      SVGGradientGenerator(
        $gradient.attr('data-gradient-style'),
        $gradient.attr('data-gradient-id'),
        $gradient.attr('data-gradient-selector')
      )
    );
  });
});

// Add markers custom icon
$doc.on('beforePrepareGoogleMapsStart.ghostkit', (e, data, $map) => {
  // each marker.
  const $markers = $map.find('.ghostkit-google-maps-marker');
  if ($markers.length) {
    $markers.each(function () {
      const $marker = $(this);
      const $infoWindow = $marker.find('.ghostkit-pro-google-maps-marker-info-window-text');
      const markerData = $marker.data();
      const newData = {};

      // icon
      if (markerData.iconUrl && markerData.iconWidth && markerData.iconHeight) {
        newData.icon = {
          url: markerData.iconUrl,
          scaledSize: new window.google.maps.Size(markerData.iconWidth, markerData.iconHeight),
        };
      }

      // info window
      if ($infoWindow.length) {
        newData.infoWindowText = $infoWindow.html();
      }

      // save data
      $marker.data(newData);
    });
  }
});

// Add markers popup
$doc.on('beforePrepareGoogleMapsEnd.ghostkit', (e, data, $map, mapObject) => {
  if (mapObject && mapObject.markers && mapObject.markers.length) {
    const infoWindow = new window.google.maps.InfoWindow();

    mapObject.markers.forEach((markerData) => {
      if (markerData.infoWindowText) {
        window.google.maps.event.addListener(markerData, 'click', () => {
          infoWindow.setContent(markerData.infoWindowText);
          infoWindow.open(mapObject, markerData);
        });
      }
    });
  }
});

// Init icons
$doc.on('beforeInitBlocks.ghostkit', () => {
  if (window.feather) {
    const $feather = $('span.feather');

    if ($feather.length) {
      $feather.each(function () {
        const $icon = $(this);
        $icon.attr('data-feather', $icon.attr('class').replace(/^feather feather-/g, ''));
        $icon.removeAttr('class');
      });
      window.feather.replace();
    }
  }
  if (window.octicons) {
    const $octicon = $('span.octicon');

    if ($octicon.length) {
      $octicon.each(function () {
        const $icon = $(this);
        $icon.attr('data-octicon', $icon.attr('class').replace(/^octicon octicon-/g, ''));
        $icon.removeAttr('class');
        window.octicons.replace([this]);
      });
    }
  }
});

// Animated text - Typed.js
$doc.on('beforeInitBlocks.ghostkit', () => {
  if (typeof Typed !== 'undefined') {
    $('.ghostkit-pro-animated-text:not(.ghostkit-pro-animated-text-ready)').each(function () {
      const $this = $(this);
      let parts = $this.attr('data-parts');

      try {
        if (parts) {
          parts = JSON.parse(parts);
        }
        // eslint-disable-next-line no-empty
      } catch (e) {}

      $this.addClass('ghostkit-pro-animated-text-ready');

      if (!parts || !parts.length) {
        return;
      }

      parts.unshift($this.html());

      const loop = $this.attr('data-loop') ? $this.attr('data-loop') === 'true' : true;
      const shuffle = $this.attr('data-shuffle') ? $this.attr('data-shuffle') === 'true' : false;
      const typeSpeed = $this.attr('data-type-speed')
        ? parseInt($this.attr('data-type-speed'), 10)
        : 40;
      const startDelay = $this.attr('data-start-delay')
        ? parseInt($this.attr('data-start-delay'), 10)
        : 0;
      const backSpeed = $this.attr('data-back-speed')
        ? parseInt($this.attr('data-back-speed'), 10)
        : 20;
      const backDelay = $this.attr('data-back-delay')
        ? parseInt($this.attr('data-back-delay'), 10)
        : 1000;
      const cursor = $this.attr('data-cursor') || false;

      $this.html('');

      // eslint-disable-next-line no-new
      new Typed($this[0], {
        strings: parts,
        typeSpeed,
        startDelay,
        backSpeed,
        backDelay,
        shuffle,
        loop,
        loopCount: false,
        showCursor: !!cursor,
        cursorChar: cursor,
      });
    });
  }
});

// Tooltips - popper.js
initTooltips();
