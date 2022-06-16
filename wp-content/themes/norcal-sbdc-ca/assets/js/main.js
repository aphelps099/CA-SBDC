


(function($) {


	$(document).ready(function() {

		$.wpchildtheme.initHeader();
		$.wpchildtheme.initContainers();

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

			// $('#header-primary-navigation-menu > li > .sub-menu').each(function(i, el) {
			// 	var subMenu = $(this);
			// 	var itemCount = subMenu.children().length;
			// 	if(itemCount >= 4) {
			// 		subMenu.addClass('double-column');
			// 	} else {
			// 		subMenu.addClass('single-column');
			// 	}
			// });

			// $(document).on('mouseenter mousemove', '#header-primary-navigation-menu > li', function(e) {
			// 	var hoveredMenuItem = $(this);
			// 	if(hoveredMenuItem.hasClass('menu-item-has-children')) {
			// 		$('body').addClass('header-dropdown-active');
			// 		var bg = $('#header > .bg');
			// 		var subMenu = $('> .sub-menu', hoveredMenuItem);
			// 		bg.css({ height: subMenu.offset().top + subMenu.outerHeight() - $('#header').offset().top + 20 });
			// 	}
			// }).on('mouseleave', '#header-primary-navigation-menu > li', function(e) {
			// 	var hoveredMenuItem = $('#header-primary-navigation-menu > li:hover');
			// 	if(!hoveredMenuItem.length || !hoveredMenuItem.hasClass('menu-item-has-children')) {
			// 		$('body').removeClass('header-dropdown-active');
			// 	}
			// });

			$('#header-primary-navigation-menu > li.has-thumbnail').each(function(i, el) {
				var menuItem = $(el);
				var img = $('> a', menuItem).data('thumbnail-img');
				$('> .sub-menu', menuItem).append('<li class="menu-item-thumbnail">' + img + '</li>');
			});

		};


		wpchildtheme.initContainers = function() {
			var adjustContainers = function() {
				var windowWidth = $('body').width();
				$('.wp-block-crown-blocks-container.container-flush-right').each(function(i, el) {
					var block = $(el);
					var container = block.parent();
					block.css({ marginRight: Math.min(0, container.offset().left + container.outerWidth() - windowWidth) });
				});
				$('.wp-block-crown-blocks-container.container-flush-left').each(function(i, el) {
					var block = $(el);
					var container = block.parent();
					block.css({ marginLeft: Math.min(0, -container.offset().left) });
				});
			};
			adjustContainers();
			$(window).on('load resize', adjustContainers);
		};


		return wpchildtheme;
		
	})({});

})(jQuery);