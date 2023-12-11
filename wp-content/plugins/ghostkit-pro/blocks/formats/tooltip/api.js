const { jQuery: $, Popper } = window;

const CLASS_TOOLTIP_BUTTON = 'ghostkit-pro-tooltip';
const CLASS_TOOLTIP_WRAP = 'ghostkit-pro-tooltip-popper-wrap';
const CLASS_TOOLTIP = 'ghostkit-pro-tooltip-popper';
const CLASS_TOOLTIP_SHOW = 'ghostkit-pro-tooltip-popper-show';
const TOOLTIP_TRANSITION = 200;

export function getTooltipFromButton($button) {
  const instance = $button.data('ghostkit-pro-tooltip');
  const timeout = $button.data('ghostkit-pro-tooltip-timeout');
  let $tooltip = false;

  if (instance && instance.state && instance.state.elements && instance.state.elements.popper) {
    $tooltip = $(instance.state.elements.popper);
  }

  return {
    instance,
    timeout,
    $tooltip,
  };
}

export function destroyTooltip($button) {
  const { $tooltip, instance } = getTooltipFromButton($button);

  if ($tooltip) {
    $tooltip.remove();
  }

  if (instance) {
    instance.destroy();
    $button.data('ghostkit-pro-tooltip', null);
  }
}

export function showTooltip($button, options) {
  const {
    $tooltip: $oldTooltip,
    instance: oldInstance,
    timeout: oldTimeout,
  } = getTooltipFromButton($button);

  if (oldTimeout) {
    clearTimeout(oldTimeout);
  }

  if (!oldInstance) {
    const label = $button.attr('aria-label');
    const $tooltip = $(
      `<div class="${CLASS_TOOLTIP_WRAP}"><div class="${CLASS_TOOLTIP}">${label}</div></div>`
    ).appendTo(options.getContainer($button));
    const $tooltipInner = $tooltip.find(`.${CLASS_TOOLTIP}`);
    const $arrow = $('<div data-popper-arrow></div>').appendTo($tooltipInner);
    const instance = Popper.createPopper($button[0], $tooltip[0], {
      placement: 'top',
      modifiers: [
        {
          name: 'arrow',
          options: {
            element: $arrow[0],
            padding: 5,
          },
        },
        {
          name: 'preventOverflow',
          options: {
            padding: 10,
          },
        },
        {
          name: 'offset',
          options: {
            offset: [0, 10],
          },
        },
        {
          name: 'flip',
          options: {
            fallbackPlacements: ['bottom'],
          },
        },
      ],
      onFirstUpdate() {
        // Trigger a reflow, flushing the CSS changes. This need to fix some glitches in Safari and Firefox.
        // Info here - https://stackoverflow.com/questions/11131875/what-is-the-cleanest-way-to-disable-css-transition-effects-temporarily
        // eslint-disable-next-line no-unused-expressions
        $tooltipInner[0].offsetHeight;

        $tooltipInner.css({
          transitionDuration: `${TOOLTIP_TRANSITION}ms`,
        });

        $tooltip.addClass(CLASS_TOOLTIP_SHOW);
      },
    });

    $button.data('ghostkit-pro-tooltip', instance);
  } else if ($oldTooltip) {
    $oldTooltip.addClass(CLASS_TOOLTIP_SHOW);
  }
}

export function hideTooltip($button) {
  const { $tooltip, instance, timeout: oldTimeout } = getTooltipFromButton($button);

  if (oldTimeout) {
    clearTimeout(oldTimeout);
  }

  if ($tooltip) {
    $tooltip.removeClass(CLASS_TOOLTIP_SHOW);
  }

  if (instance) {
    const timeout = setTimeout(() => {
      $button.data('ghostkit-pro-tooltip-timeout', null);
      destroyTooltip($button);
    }, TOOLTIP_TRANSITION);

    $button.data('ghostkit-pro-tooltip-timeout', timeout);
  }
}

export function initTooltips(defaultOptions = {}) {
  if (typeof Popper === 'undefined') {
    return;
  }

  const options = {
    getContainer() {
      return $('body');
    },
    ...defaultOptions,
  };

  $(document).on('mouseenter focus', `.${CLASS_TOOLTIP_BUTTON}[aria-label]`, function () {
    const $this = $(this);
    const label = $this.attr('aria-label');

    if (!label) {
      return;
    }

    showTooltip($this, options);
  });
  $(document).on('mouseleave blur', `.${CLASS_TOOLTIP_BUTTON}[aria-label]`, function () {
    hideTooltip($(this), options);
  });
}
