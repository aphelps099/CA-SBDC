

(function($) {


	$(document).ready(function() {

		$.wpchildtheme.initHeader();
		$.wpchildtheme.initContentSliderBlocks();
		$.wpchildtheme.initFeaturedPostSliderBlocks();

		$(document).on('click', 'a', function(e) {
			var href = $(this).attr('href');
			if(href.match(/^#.+/) && $(href).length) {
				e.preventDefault();
				$.wptheme.smoothScrollToElement($(href));
			}
		});

	});


	$.wpchildtheme = (function(wpchildtheme) {


		wpchildtheme.initHeader = function() {

			var header = $('#header');
			var first_section = $('#main-content').children().first();
			// if(first_section.is('.wp-block-crown-blocks-event-header')) header.removeClass('text-color-light');

			$('#header-primary-navigation-menu > li > .sub-menu').each(function(i, el) {
				var subMenu = $(this);
				var itemCount = subMenu.children().length;
				if(itemCount >= 4) {
					subMenu.addClass('double-column');
				} else {
					subMenu.addClass('single-column');
				}
			});

			$(document).on('mouseenter mousemove', '#header-primary-navigation-menu > li', function(e) {
				var hoveredMenuItem = $(this);
				if(hoveredMenuItem.hasClass('menu-item-has-children')) {
					$('body').addClass('header-dropdown-active');
					var bg = $('#header > .bg');
					var subMenu = $('> .sub-menu', hoveredMenuItem);
					bg.css({ height: subMenu.offset().top + subMenu.outerHeight() - $('#header').offset().top + 20 });
				}
			}).on('mouseleave', '#header-primary-navigation-menu > li', function(e) {
				var hoveredMenuItem = $('#header-primary-navigation-menu > li:hover');
				if(!hoveredMenuItem.length || !hoveredMenuItem.hasClass('menu-item-has-children')) {
					$('body').removeClass('header-dropdown-active');
				}
			});

		};


		wpchildtheme.initContentSliderBlocks = function() {
			$('.wp-block-crown-blocks-content-slider').each(function(i, el) {
				var slider = $('> .inner', el);
				if(slider.hasClass('slick-initialized-ip')) return;
				if(slider.hasClass('slick-initialized')) { slider.slick('unslick'); }
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: false,
					arrows: true,
					fade: true,
					adaptiveHeight: false
				};
				slider.on('setPosition', function(event, slick) {
					var track = $('.slick-track', slick.$slider);
					var slides = $('.slick-slide', slick.$slider);
					slides.css({ height: 'auto' });
					slides.css({ height: track.height() });
				}).slick(slickSettings).addClass('slick-initialized-ip');
			});
		};


		wpchildtheme.initFeaturedPostSliderBlocks = function() {
			$('.wp-block-crown-blocks-featured-post-slider').each(function(i, el) {
				var slider = $('.post-feed > .inner', el);
				if(slider.hasClass('slick-initialized-ip')) return;
				if(slider.hasClass('slick-initialized')) { slider.slick('unslick'); }
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: false,
					arrows: true,
					// fade: true,
					slidesToShow: 1,
					slidesToScroll: 1,
					adaptiveHeight: false,
					responsive: [
						{ breakpoint: 768 - 1,  settings: { slidesToShow: 2, slidesToScroll: 2 } }
					]
				};
				slider.on('setPosition', function(event, slick) {
					var track = $('.slick-track', slick.$slider);
					var slides = $('.slick-slide', slick.$slider);
					slides.css({ height: 'auto' });
					slides.css({ height: track.height() });
				}).slick(slickSettings);
			});
		};



		return wpchildtheme;
		
	})({});

})(jQuery);