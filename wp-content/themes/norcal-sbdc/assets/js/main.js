

(function($) {

	var stickyHeaderScrollTracker = 0;
	var keepStickyHeaderHidden = false;
	var mapSettings = null;


	$(document).ready(function() {

		$.wptheme.initSiteAnnouncement();
		$.wptheme.initHeader();
		$.wptheme.initMobileMenu();

		$.wptheme.initGalleries();
		$.wptheme.initSliders();
		$.wptheme.initModals();
		$.wptheme.initSocialSharingLinks();
		$.wptheme.initPostFeeds();
		
		$.wptheme.initContainerBlocks();
		// $.wptheme.initExpandableContentBlocks();
		$.wptheme.initTabbedContentBlocks();
		$.wptheme.initTestimonialSliderBlocks();
		$.wptheme.initContentSliderBlocks();
		$.wptheme.initTwoColumnScrollSliderBlocks();
		$.wptheme.initFeaturedPostSliderBlocks();
		$.wptheme.initFeaturedResourceSliderBlocks();
		$.wptheme.initCenterFinderBlocks();
		$.wptheme.initSectionNavBlocks();
		$.wptheme.initTeamMemberIndexBlocks();

		$.wptheme.initBranchMapShortcodes();

		$.wptheme.initHeaders();
		$.wptheme.initOdometers();

	});


	$(window).load(function() {

		

	});


	$.wptheme = (function(wptheme) {


		wptheme.initMapSettings = function() {
			if(mapSettings) return;
			if(typeof google === 'undefined') return;
			mapSettings = {
				markerShapes: {
					default: {
						coords: [ 15,0 , 26,4 , 30,15 , 26,19 , 15,37 , 4,19 , 0,15 , 4,4 ],
						type: 'poly'
					}
				},
				markerImages: {
					darkBlue: {
						url: crownThemeData.themeUrl + '/assets/img/icons/map-marker-dark-blue.png',
						size: new google.maps.Size(30, 37),
						scaledSize: new google.maps.Size(30, 37),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(15, 37)
					}
				}
			};
			mapSettings.markerImages.blue = $.extend(true, {}, mapSettings.markerImages.darkBlue, { url: crownThemeData.themeUrl + '/assets/img/icons/map-marker-blue.png' });
			mapSettings.markerImages.red = $.extend(true, {}, mapSettings.markerImages.darkBlue, { url: crownThemeData.themeUrl + '/assets/img/icons/map-marker-red.png' });
			mapSettings.markerImages.gray = $.extend(true, {}, mapSettings.markerImages.darkBlue, { url: crownThemeData.themeUrl + '/assets/img/icons/map-marker-gray.png' });
			mapSettings.markerImages.white = $.extend(true, {}, mapSettings.markerImages.darkBlue, { url: crownThemeData.themeUrl + '/assets/img/icons/map-marker-white.png' });
		};


		wptheme.initSiteAnnouncement = function() {

			$(document).on('click', '#site-announcement button.dismiss', function(e) {
				var announcement = $('#site-announcement');
				announcement.removeClass('shown');
				setTimeout(function() { announcement.addClass('active'); }, 500);
				var id = announcement.data('announcement-id');
				setCookie('site_announcement_' + id, 'dismissed', 365);
			});

			var adjustAnnouncement = function() {
				var announcement = $('#site-announcement');
				if(announcement.length && announcement.hasClass('active')) {
					var height = announcement.outerHeight();
					announcement.css({ marginTop: -height });
				}
			};

			var announcement = $('#site-announcement');
			if(announcement.length) {
				var id = announcement.data('announcement-id');
				var status = getCookie('site_announcement_' + id);
				if(status != 'dismissed') {
					announcement.addClass('active');
					setTimeout(function() { announcement.addClass('shown'); }, 500);
					adjustAnnouncement();
					$(window).on('load resize', adjustAnnouncement);
				}
			}
			
		};


		wptheme.initHeader = function() {

			var header = $('#header');
			var first_section = $('#main-content').children().first();
			if(first_section.is('.wp-block-crown-blocks-container.text-color-light.alignfull')) header.addClass('text-color-light');
			if(first_section.is('.wp-block-crown-blocks-post-header.text-color-light')) header.addClass('text-color-light');
			if(first_section.is('.wp-block-crown-blocks-resource-header.text-color-light')) header.addClass('text-color-light');
			if(first_section.is('.wp-block-crown-blocks-event-header')) header.addClass('text-color-light');
			if(first_section.is('.wp-block-crown-blocks-client-story-header')) header.addClass('text-color-light');
			header.addClass('loaded');

			var adjustSubMenus = function() {
				var windowWidth = $('body').width();
				var gap = 10;
				$('#header-primary-navigation .sub-menu:not(.mega-sub-menu)').each(function(i, el) {
					var subMenu = $(el);
					var isSubSubMenu = subMenu.parent().closest('.sub-menu').length;
					subMenu.css({ marginLeft: 0 });
					if(isSubSubMenu) subMenu.removeClass('drop-left');
					if(subMenu.offset().left + subMenu.outerWidth() > windowWidth - gap) {
						if(isSubSubMenu) {
							subMenu.addClass('drop-left');
						} else {
							subMenu.css({ marginLeft: (windowWidth - (subMenu.offset().left + subMenu.outerWidth()) - gap) + 'px' });
						}
					}
				});
			};

			adjustSubMenus();
			$(window).on('load', adjustSubMenus);
			$(window).on('resize', adjustSubMenus);

			// // activate sub-menus on hover
			// $('#header-primary-navigation .menu-item').on('mouseenter', function(e) {
			// 	var menuItem = $(this);
			// 	var subMenu = $('> .sub-menu', menuItem);
			// 	if(subMenu.length) {
			// 		menuItem.addClass('active');
			// 	}
			// }).on('mouseleave', function(e) {
			// 	var menuItem = $(this);
			// 	if(menuItem.hasClass('active')) {
			// 		menuItem.removeClass('active');
			// 	}
			// });

			// activate sub-menus on click
			$('#header-primary-navigation a').on('click', function(e) {
				var menuItem = $(this).closest('.menu-item');
				var subMenu = $('> .sub-menu', menuItem);
				$('#header-primary-navigation .menu-item.active').not($(this).parents('.menu-item.active')).removeClass('active');
				if(subMenu.length && !menuItem.hasClass('active')) {
					e.preventDefault();
					menuItem.addClass('active');
				}
			});
			$(document).on('click', function(e) {
				if($(e.target).closest('#header-primary-navigation-menu').length) return;
				$('#header-primary-navigation .menu-item.active').removeClass('active');
			});

			var updateHeaderStatus = function() {
				var header = $('#header');
				var scrollTop = $(window).scrollTop();
				var threshold = 0;
				if($('body').width() < 601 && $('#wpadminbar').length) threshold += $('#wpadminbar').outerHeight();
				if($('#site-announcement.active.shown').length) threshold += $('#site-announcement.active.shown').outerHeight();
				if(scrollTop > threshold && !header.hasClass('is-sticky')) {
					header.addClass('is-sticky');
				} else if(scrollTop <= threshold && header.hasClass('is-sticky')) {
					header.removeClass('is-sticky');
				}
				if(scrollTop > stickyHeaderScrollTracker && scrollTop > threshold + 300 && !$('#header-primary-navigation-menu > .menu-item.active').length && !header.hasClass('is-minified')) {
					header.addClass('is-minified');
				} else if(scrollTop <= stickyHeaderScrollTracker && header.hasClass('is-minified') && !keepStickyHeaderHidden) {
					header.removeClass('is-minified');
				}
				stickyHeaderScrollTracker = scrollTop;
			};
			updateHeaderStatus();
			$(window).on('load scroll', updateHeaderStatus);

		};


		wptheme.initMobileMenu = function() {

			$(document).on('click', '#mobile-menu-toggle', function(e) {
				var body = $('body');
				if(body.is('.mobile-menu-active')) {
					$(document).trigger('close-mobile-menu');
				} else {
					body.addClass('mobile-menu-active');
				}
			});

			$(document).on('click', '#mobile-menu-close', function(e) {
				$(document).trigger('close-mobile-menu');
			});

			$(document).on('touchstart click', 'body.mobile-menu-active #page', function(e) {
				if($(e.target).closest('#mobile-menu-toggle').length) return;
				$(document).trigger('close-mobile-menu');
			});

			$(document).on('close-mobile-menu', function() {
				var body = $('body');
				body.removeClass('mobile-menu-active');
			});

			$('#mobile-menu-primary-navigation .menu-item').each(function(i, el) {
				var menuItem = $(this);
				var subMenu = $('> .sub-menu', menuItem);
				if(subMenu.length) {
					menuItem.addClass('menu-item-has-sub-menu');
					menuItem.append('<button type="button" class="toggle">Toggle</button>');
				}
			});

			$('#mobile-menu-primary-navigation').on('click', 'button.toggle', function(e) {
				var menuItem = $(this).closest('.menu-item');
				var subMenu = $('> .sub-menu', menuItem);
				if(!menuItem.hasClass('active')) {
					menuItem.addClass('active');
					var startHeight = subMenu.outerHeight();
					subMenu.css({ height: 'auto' });
					var endHeight = subMenu.outerHeight();
					subMenu.css({ height: startHeight });
					setTimeout(function() { subMenu.css({ height: endHeight }); }, 10);
					setTimeout(function() { subMenu.css({ height: 'auto' }); }, 210);
				} else {
					menuItem.removeClass('active');
					var startHeight = subMenu.outerHeight();
					var endHeight = 0;
					subMenu.css({ height: startHeight });
					setTimeout(function() { subMenu.css({ height: endHeight }); }, 10);
				}
			});

			$('#mobile-menu-primary-navigation .menu-item-has-sub-menu.current-menu-item, #mobile-menu-primary-navigation .menu-item-has-sub-menu.current-menu-ancestor').each(function(i, el) {
				$('> .toggle', el).trigger('click');
			});

		};


		wptheme.initGalleries = function() {

			

			// var caseStudyGalleryCallback = function(e) {
			// 	var images = $(this).closest('.entry-photos').find('.image').not('.slick-cloned');
			// 	var galleryLinks = [];
			// 	var index = 0;
			// 	images.each(function(i, el) {
			// 		galleryLinks.push({
			// 			title: $('.caption', el).length ? $('.caption', el).text() : '',
			// 			href: $('img.large', el).attr('src'),
			// 			thumbnail: $('img.thumbnail', el).attr('src')
			// 		});
			// 		if($(el).hasClass('slick-current')) index = i;
			// 	});
			// 	blueimp.Gallery(galleryLinks, {
			// 		index: index
			// 	});
			// };
			// $(document).on('click', '.wp-block-crown-blocks-featured-case-studies article .entry-photos .expand-image', caseStudyGalleryCallback);
			// $(document).on('click', '.wp-block-crown-blocks-case-study-index article .entry-photos .expand-image', caseStudyGalleryCallback);
			// $(document).on('click', '.modal.case-study article .entry-photos .expand-image', caseStudyGalleryCallback);

		};


		wptheme.initSliders = function() {

			

		};


		wptheme.initModals = function() {

			$(document).on('submit', '#footer-subscribe-form form', function(e) {
				e.preventDefault();
				var form = $(this);
				var formId = form.attr('id').replace(/^gform_/, '');
				var email = $('.ginput_container_email input', form).val();
				var modal = $('#subscribe-modal');
				if(modal.length) {
					$('.ginput_container_email input', modal).val(email);
					modal.modal('show');
				}
				window['gf_submitting_' + formId] = false;
				return false;
			});

		};


		wptheme.initSocialSharingLinks = function() {

			$(document).on('click', '.social-sharing-links a', function(e) {
				var link = $(this);
				var li = link.parent();
				var ul = li.parent();
				if(li.hasClass('print')) {
					e.preventDefault();
					window.print();
				} else if(li.is('.facebook, .twitter, .linkedin, .google-plus, .pinterest, .houzz')) {
					e.preventDefault();
					var winWidth = 600;
					var winHeight = 400;
					if(li.hasClass('google-plus')) { winWidth = 512; }
					if(li.hasClass('pinterest')) { winWidth = 750; }
					if(li.hasClass('houzz')) { winWidth = 800; winHeight = 460; }
					var winTop = (screen.height / 2) - (winHeight / 2) - (screen.height * 0.1);
					var winLeft = (screen.width / 2) - (winWidth / 2);
					window.open($(this).attr('href'), 'Share Link', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
				}
			});

			var adjustStickyShareLinks = function() {
				var footerTop = $('#pre-footer').offset().top;
				$('.wp-block-crown-blocks-post-header .sticky-share-links').each(function(i, el) { $(el).height(footerTop - $(el).offset().top - 200); });
				$('.wp-block-crown-blocks-resource-header .sticky-share-links').each(function(i, el) { $(el).height(footerTop - $(el).offset().top - 200); });
			}
			adjustStickyShareLinks();
			$(window).on('load resize', adjustStickyShareLinks);

		};


		wptheme.initMap = function(map) {
			wptheme.initMapSettings();
			if(typeof google === 'undefined') return;

			map.on('mapInitialized', function(e) {
				var mapData = $(this).data('map-data');
				if(mapData.settings.points.length && !mapData.markers.length) {

					var infoWindow = null;

					if(typeof InfoBox !== 'undefined') {

						infoWindow = new InfoBox({
							pixelOffset: new google.maps.Size(-120, -50),
							alignBottom: true,
							closeBoxURL: ''
						});

						google.maps.event.addListener(mapData.map, 'click', function() {
							infoWindow.close();
						});

					}

					mapData.bounds = new google.maps.LatLngBounds();
					for(var i in mapData.settings.points) {
						var point = mapData.settings.points[i];

						var position = new google.maps.LatLng(point.lat, point.lng);
						var markerSettings = {
							markerIndex: parseInt(i),
							position: position,
							map: mapData.map,
							icon: mapSettings.markerImages.red,
							shape: mapSettings.markerShapes.default,
							title: point.title,
							data: point
						};
						var marker = new google.maps.Marker(markerSettings);

						if(infoWindow && markerSettings.data.infoBoxContent) {
							google.maps.event.addListener(marker, 'click', function() {
								infoWindow.setOptions({ boxClass: 'infoBox' });
								infoWindow.setContent(this.data.infoBoxContent);
								infoWindow.open(this.map, this);
								setTimeout(function() { infoWindow.setOptions({ boxClass: 'infoBox active' }); }, 100);
							});
						}

						if($(mapData.map.getDiv()).closest('.wp-block-crown-blocks-center-finder').length) {
							google.maps.event.addListener(marker, 'click', function() {
								var block = $(this.map.getDiv()).closest('.wp-block-crown-blocks-center-finder');
								var location = $('.locations article.location[data-location-id="' + this.data.locationId + '"]', block);
								if(location.length) {
									location.trigger('click');
									var offset = 32;
									if(location.offset().top - 32 < $(window).scrollTop()) {
										wptheme.smoothScrollToPos(location.offset().top - 32);
									} else if(location.offset().top + location.outerHeight() + 32 > $(window).scrollTop() + $(window).height()) {
										wptheme.smoothScrollToPos(location.offset().top + location.outerHeight() + 32 - $(window).height());
									}
									// $('.locations', block).stop(true).animate({ scrollTop: location.offset().top - location.parent().offset().top }, 1000, 'easeOutExpo');
								}
							});
						}

						mapData.markers.push(marker);
						mapData.bounds.extend(position);
					}

					if(mapData.settings.points.length) {

						mapData.map.fitBounds(mapData.bounds);

						google.maps.event.addListenerOnce(mapData.map, 'idle', function() { 
							var mapData = $(this.getDiv()).data('map-data');
							if(mapData.map.getZoom() > mapData.settings.options.zoom) {
								mapData.map.setZoom(mapData.settings.options.zoom);
							}
						});

					}

				}
			});

		};


		wptheme.initPostFeeds = function() {

			$('.wp-block-crown-blocks-post-index').on('setArticleColors', function(e) {
				var block = $(this);
				if(block.hasClass('wp-block-crown-blocks-post-index')) {
					$('.post-feed article:not(.post-format-tweet):not(.post-format-facebook-update) a', block).each(function(i, el) {
						if($(this).hasClass('color-set')) return;
						var colors = [ 'blue', 'red', 'gray', 'dark-gray' ];
						var color = Math.floor(Math.random() * 2) == 0 ? 'dark-blue' : colors[Math.floor(Math.random() * Math.floor(colors.length))];
						$(this).addClass('color-set color-' + color);
					});
				}
			});
			$('.wp-block-crown-blocks-post-index').trigger('setArticleColors');

			$(document).on('click', 'form.feed-filters button.filters-toggle', function(e) {
				var form = $(this).closest('form');
				form.toggleClass('active');
				var tabs = $('.filters-tabs', form)
				if(!form.hasClass('active')) {
					tabs.height(tabs.height());
					setTimeout(function() { tabs.height(0); }, 10);
				} else {
					tabs.height(0);
					tabs.height($('> .inner', tabs).outerHeight());
					setTimeout(function() { tabs.css({ height: 'auto' }); }, 300);
				}

				var hasActiveFilters = false;
				if($('input[type=text]', form).filter(function(i, el) { return $(el).val() != '' }).length) hasActiveFilters = true;
				if($('input[type=checkbox]:checked', form).length) hasActiveFilters = true;
				if(hasActiveFilters) {
					form.addClass('has-active-filters');
				} else {
					form.removeClass('has-active-filters');
				}

			});

			$(document).on('click', 'form.feed-filters button.filters-clear', function(e) {
				var form = $(this).closest('form');
				$('input[type=text]', form).val('');
				$('input[type=checkbox]', form).prop('checked', false);
				form.trigger('submit');
			});

			$(document).on('click', 'form.feed-filters button.filters-close', function(e) {
				var form = $(this).closest('form');
				if(!form.hasClass('active')) return;
				form.removeClass('active');
				var tabs = $('.filters-tabs', form)
				tabs.height(tabs.height());
				setTimeout(function() { tabs.height(0); }, 100);
			});

			$(document).on('click', 'form.feed-filters .filters-nav button', function(e) {
				var menuItem = $(this).closest('li');
				if(menuItem.hasClass('active')) return;
				var form = $(this).closest('form');
				var key = $(this).data('tab');
				$('.filters-nav li.active', form).removeClass('active');
				menuItem.addClass('active');
				$('.filters-tab.active', form).removeClass('active');
				$('.filters-tab', form).filter(function(i, el) { return $(el).data('tab') == key; }).addClass('active');
			});
			$('form.feed-filters .filters-nav').each(function(i, el) { $('button:first', el).trigger('click'); });

			$(document).on('change', 'form.feed-filters ul.options.singular input', function(e) {
				var list = $(this).closest('ul');
				$('input', list).not($(this)).prop('checked', false);
			});

			$(document).on('change', 'form.feed-filters ul.options.singular input', function(e) {
				var list = $(this).closest('ul');
				$('input', list).not($(this)).prop('checked', false);
			});

			$(document).on('change', '.post-feed-block form.feed-filters input', function(e) { $(this).closest('form').trigger('submit'); });
			$(document).on('submit', '.post-feed-block form.feed-filters', function(e) {
				e.preventDefault();
				var queryString = $(this).serialize();
				var url = $(this).attr('action');
				if(queryString != '') url += ($(this).attr('action').match(/\?/) ? '&' : '?') + queryString;
				wptheme.updatePostFeedBlock($(this).closest('.post-feed-block'), url);

				var hasActiveFilters = false;
				if($('input[type=text]', this).filter(function(i, el) { return $(el).val() != '' }).length) hasActiveFilters = true;
				if($('input[type=checkbox]:checked', this).length) hasActiveFilters = true;
				if(hasActiveFilters) {
					$(this).addClass('has-active-filters');
				} else {
					$(this).removeClass('has-active-filters');
				}

			});
			$(document).on('click', '.post-feed-block .pagination a', function(e) {
				e.preventDefault();
				wptheme.updatePostFeedBlock($(this).closest('.post-feed-block'), $(this).attr('href'));
			});

		};
		wptheme.updatePostFeedBlock = function(block, url) {
			if(url.match(/^\//)) url = crownThemeData.baseUrl + url;
			var blockId = block.attr('id');
			$('.ajax-loader', block).addClass($('.ajax-loader', block).hasClass('infinite') && url.match(/\/page\/\d+\//) ? 'loading-page' : 'loading');
			if(history.replaceState != null) {
				history.replaceState('', document.title, url);
			}
			$.get(crownThemeData.ajaxUrl, { action: 'get_ajax_content', url: url, id: blockId }, function(response) {
				var blockId = response.id;
				var block = $('#' + blockId);
				if(block.length) {
					if(response.content) {
						var content = $('#' + blockId + ' .ajax-content', response.content);
						if(content.length) {
							var infiniteLoaderContainer = $('.ajax-content .infinite-loader-container', block);
							if(infiniteLoaderContainer.length && response.url.match(/\/page\/\d+\//)) {
								infiniteLoaderContainer.append($('.infinite-loader-container', content).children());
								$('.pagination-wrapper', block).remove();
								if($('.pagination-wrapper.infinite', content).length) {
									$('.ajax-content', block).append($('.pagination-wrapper.infinite', content));
								}
							} else {
								$('.ajax-content', block).html(content.html());
								if(block.offset().top < $(window).scrollTop()) {
									wptheme.smoothScrollToElement(block);
								}
							}
							block.trigger('setArticleColors');
						}
					}
					$('.ajax-loader', block).removeClass('loading loading-page');
				}
			}, 'json');
		};


		wptheme.initContainerBlocks = function() {

			var adjustContainerBGs = function() {
				var windowWidth = $('body').width();
				$('.wp-block-crown-blocks-container.bg-flush-right').each(function(i, el) {
					var container = $(el);
					$('> .container-bg', container).css({ right: Math.min(0, container.offset().left + container.outerWidth() - windowWidth) });
				});
				$('.wp-block-crown-blocks-container.bg-flush-left').each(function(i, el) {
					var container = $(el);
					$('> .container-bg', container).css({ left: Math.min(0, -container.offset().left) });
				});
			};
			adjustContainerBGs();
			$(window).on('load resize', adjustContainerBGs);

		};


		wptheme.initExpandableContentBlocks = function() {
			$(document).on('click', '.wp-block-crown-blocks-expandable-content .expandable-content-toggle button', function() {
				var container = $(this).closest('.wp-block-crown-blocks-expandable-content');
				var contents = $('> .inner > .expandable-content-contents', container);
				container.css({ minHeight: container.outerHeight() });
				container.addClass('expanded');
				contents.css({ height: $('> .inner', contents).outerHeight() });
				setTimeout(function() {
					contents.css({ height: 'auto' });
				}, 500);
			});
		};


		wptheme.initTabbedContentBlocks = function() {

			$('.wp-block-crown-blocks-tabbed-content:not(.type-grid)').each(function(i, el) {
				var slider = $('.tabbed-content-tabs > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var sliderNavContainer = $('<div class="slick-slider-nav"></div>');
				$('.tabbed-content-tabs', el).before(sliderNavContainer);
				slider.children().wrap('<div></div>');
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: true,
					arrows: false,
					fade: true,
					appendDots: sliderNavContainer,
					adaptiveHeight: true,
					customPaging: function(slider, pageIndex) {
						var tabTitleEl = $('> .wp-block-crown-blocks-tabbed-content-tab > .inner > .tab-title', slider.$slides[pageIndex]);
						var title = tabTitleEl ? tabTitleEl.text() : '';
						var tab = $('<button type="button"></button');
						tab.append('<span class="index">' + padNumber(pageIndex + 1, 2) + '<span>');
						if(title != '') tab.append(' <span class="label">' + title + '<span>');
						tab.append(' <span class="indicator"><span></span></span>');
						return tab;
					}
				};
				slider.slick(slickSettings);
			});

			$('.wp-block-crown-blocks-tabbed-content.type-grid').each(function(i, el) {
				var block = $(el);
				var nav = $('<nav class="tabbed-content-nav"><ul class="menu"></ul></nav>');
				$('> .inner', block).prepend(nav);
				$('> .inner > .tabbed-content-tabs > .inner > .wp-block-crown-blocks-tabbed-content-tab', block).each(function(j, el2) {
					var tab = $(el2);
					var title = $('> .inner > .tab-title', tab).length ? $('> .inner > .tab-title', tab).text() : '';
					var tabButton = $('<button type="button"><span class="inner"></span></button>');
					tabButton.data('tab-index', j);
					tab.data('tab-index', j);
					$('> .inner', tabButton).append('<span class="index">' + padNumber(j + 1, 2) + '<span>');
					if(title != '') $('> .inner', tabButton).append(' <span class="label">' + title + '<span>');
					$('> .inner', tabButton).append(' <span class="indicator"><span></span></span>');
					$('.menu', nav).append(tabButton);
					tabButton.wrap('<li class="menu-item"></li>');
				});
			});
			$(document).on('click', '.wp-block-crown-blocks-tabbed-content.type-grid > .inner > .tabbed-content-nav button', function(e) {
				var block = $(this).closest('.wp-block-crown-blocks-tabbed-content');
				var menuItem = $(this).closest('.menu-item');
				var menu = $(this).closest('.menu');
				var tabIndex = $(this).data('tab-index');
				if(menuItem.hasClass('active')) {
					menuItem.removeClass('active');
					$('.drawer', menu).each(function(i, el) {
						var oldDrawer = $(el);
						$('> .inner', oldDrawer).css({ height: oldDrawer.height() });
						oldDrawer.removeClass('active');
						setTimeout(function() { $('> .inner', oldDrawer).css({ height: 0 }) }, 100);
						setTimeout(function() { oldDrawer.remove(); }, 600);
					});
				} else {
					$('.menu-item.active', menu).removeClass('active');
					menuItem.addClass('active');
					var tabContent = $('> .inner > .tabbed-content-tabs > .inner > .wp-block-crown-blocks-tabbed-content-tab', block).eq(tabIndex).clone();
					var lirItem = menuItem;
					while(lirItem.nextAll('.menu-item').length && lirItem.nextAll('.menu-item').first().offset().top == menuItem.offset().top) {
						lirItem = lirItem.nextAll('.menu-item').first();
					}
					var drawer = lirItem.next('.drawer');
					if(!drawer.length) {
						drawer = $('<li class="drawer"><div class="inner"></div></li>');
						lirItem.after(drawer);
						if(drawer.closest('.text-color-light, .text-color-dark').hasClass('text-color-light')) {
							drawer.addClass('text-color-dark');
						} else {
							drawer.addClass('text-color-light');
						}
					}
					$('.drawer', menu).not(drawer).each(function(i, el) {
						var oldDrawer = $(el);
						oldDrawer.hide();
						var scrollTo = menuItem.offset().top;
						oldDrawer.show();
						$('> .inner', oldDrawer).css({ height: oldDrawer.height() });
						oldDrawer.removeClass('active');
						setTimeout(function() { $('> .inner', oldDrawer).css({ height: 0 }) }, 100);
						setTimeout(function() { oldDrawer.remove(); }, 500);
						wptheme.smoothScrollToPos(scrollTo);
					});
					$('> .inner', drawer).css({ height: drawer.hasClass('active') ? drawer.height() : 0 });
					$('> .inner', drawer).html(tabContent);
					$(' > .inner', tabContent).css({ maxWidth: menu.parent().width() });
					drawer.addClass('active');
					setTimeout(function() { $('> .inner', drawer).css({ height: tabContent.outerHeight() }) }, 100);
					setTimeout(function() { $('> .inner', drawer).css({ height: 'auto' }) }, 600);
				}
			});
			$(window).on('resize', function() {
				$('.wp-block-crown-blocks-tabbed-content.type-grid .tabbed-content-nav .drawer').each(function(i, el) {
					var drawer = $(el);
					var menu = drawer.closest('.menu');
					var menuItem = $('> .menu-item.active', menu);
					drawer.hide();
					var lirItem = menuItem;
					while(lirItem.nextAll('.menu-item').length && lirItem.nextAll('.menu-item').first().offset().top == menuItem.offset().top) {
						lirItem = lirItem.nextAll('.menu-item').first();
					}
					if(!lirItem.next('.drawer').length || !lirItem.next('.drawer').is(drawer)) {
						lirItem.after(drawer);
					}
					drawer.show();
					$('> .inner > .wp-block-crown-blocks-tabbed-content-tab > .inner').css({ maxWidth: menu.parent().width() });
				});
			});

		};


		wptheme.initTestimonialSliderBlocks = function() {
			$('.wp-block-crown-blocks-testimonial-slider').each(function(i, el) {
				var slider = $('.testimonial-slider-testimonials > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var sliderNavContainer = $('<div class="slick-slider-nav"></div>');
				$('.testimonial-slider-testimonials', el).before(sliderNavContainer);
				slider.children().wrap('<div></div>');
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: false,
					arrows: true,
					fade: true,
					appendArrows: sliderNavContainer,
					adaptiveHeight: true
				};
				slider.on('init', function(e, slick) {
					var block = slick.$slider.closest('.wp-block-crown-blocks-testimonial-slider');
					var nav = $('.slick-slider-nav', block);
					$('.status', nav).remove();
					if(slick.slideCount > 1) {
						nav.append('<span class="status"><span class="current">' + (slick.currentSlide + 1) + '</span> <span class="of">of</span> <span class="total">' + slick.slideCount + '</span></span>')
					}
				}).on('beforeChange', function(e, slick, currentSlide, nextSlide) {
					var block = slick.$slider.closest('.wp-block-crown-blocks-testimonial-slider');
					var nav = $('.slick-slider-nav', block);
					$('.status', nav).remove();
					if(slick.slideCount > 1) {
						nav.append('<span class="status"><span class="current">' + (nextSlide + 1) + '</span> <span class="of">of</span> <span class="total">' + slick.slideCount + '</span></span>')
					}
				}).slick(slickSettings);
			});
		};


		wptheme.initContentSliderBlocks = function() {
			$('.wp-block-crown-blocks-content-slider').each(function(i, el) {
				var slider = $('> .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var slickSettings = {
					mobileFirst: true,
					draggable: true,
					dots: true,
					arrows: false,
					fade: false
				};
				slider.slick(slickSettings);
			});
		};

		
		wptheme.initTwoColumnScrollSliderBlocks = function() {

			$('.wp-block-crown-blocks-two-column-scroll-slider').each(function(i, el) {
				var block = $(el);
				var static = $('> .inner', block);
				var slider = static.clone();
				slider.addClass('slider');
				
				var col1 = $('<div class="column"></div>');
				var col2 = $('<div class="column"></div>');
				var slides = $('> .wp-block-crown-blocks-two-column-scroll-slider-slide', slider);
				slides.each(function(j, el2) {
					col1.append($('> .inner > .wp-block-crown-blocks-container:eq(0)', el2));
					col2.append($('> .inner > .wp-block-crown-blocks-container:eq(0)', el2));
				});
				slides.remove();
				slider.append(col1).append(col2);

				static.addClass('static');
				block.append(slider);
			});

			var adjustTwoColumnScrollSliderSlides = function() {
				var windowHeight = $(window).height();
				var windowWidth = $('body').width();
				$('.wp-block-crown-blocks-two-column-scroll-slider > .inner.slider').each(function(i, el) {
					var slider = $(el);
					var col1 = $('> .column:eq(0)', slider);
					var col2 = $('> .column:eq(1)', slider);
					var offset = $('html').offset().top;
					if(windowWidth >= 768 && slider.closest('.wp-block-crown-blocks-section-nav').length) {
						offset += slider.closest('.wp-block-crown-blocks-section-nav').find('> .inner > .section-nav-nav').outerHeight();
					}
					$('> .column > .wp-block-crown-blocks-container', slider).css({ minHeight: windowHeight - offset });
					$('> .wp-block-crown-blocks-container', col1).each(function(j, el2) {
						var container1 = $('> .wp-block-crown-blocks-container:eq(' + j + ')', col1);
						var container2 = $('> .wp-block-crown-blocks-container:eq(' + j + ')', col2);
						container1.css({ height: 'auto' });
						container2.css({ height: 'auto' });
						var maxHeight = Math.max(container1.outerHeight(), container2.outerHeight());
						container1.css({ height: maxHeight });
						container2.css({ height: maxHeight });
						if(maxHeight <= windowHeight - offset) {
							container1.addClass('sticky').css({ top: offset });
							container2.addClass('sticky').css({ top: offset });
						} else {
							container1.removeClass('sticky').css({ top: 0 });
							container2.removeClass('sticky').css({ top: 0 });
						}
					});
				});
			};
			adjustTwoColumnScrollSliderSlides();
			$(window).on('load resize', adjustTwoColumnScrollSliderSlides);

		};


		wptheme.initFeaturedPostSliderBlocks = function() {

			var adjustFeaturedPostSliders = function() {
				var windowWidth = $('body').width();
				$('.wp-block-crown-blocks-featured-post-slider.slider-flush-right').each(function(i, el) {
					var block = $(el);
					var container = $('.post-feed', el);
					container.css({ marginRight: Math.min(0, block.offset().left + block.outerWidth() - windowWidth) });
				});
			};
			adjustFeaturedPostSliders();
			$(window).on('load resize', adjustFeaturedPostSliders);

			$('.wp-block-crown-blocks-featured-post-slider').each(function(i, el) {
				var slider = $('.post-feed > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var sliderNav = $('<div class="post-feed-nav"></div>');
				slider.parent().after(sliderNav);
				var slickSettings = {
					mobileFirst: true,
					draggable: true,
					dots: false,
					arrows: true,
					appendArrows: sliderNav,
					fade: false,
					slidesToShow: 1,
					slidesToScroll: 1,
					responsive: [
						{ breakpoint: 768 - 1,  settings: { slidesToShow: 2, slidesToScroll: 2 } },
						{ breakpoint: 992 - 1,  settings: { slidesToShow: 3, slidesToScroll: 3 } }
					]
				};
				slider.slick(slickSettings);
			});

		};


		wptheme.initFeaturedResourceSliderBlocks = function() {
			$('.wp-block-crown-blocks-featured-resource-slider').each(function(i, el) {
				var slider = $('.post-feed > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var slickSettings = {
					mobileFirst: true,
					draggable: true,
					dots: false,
					arrows: true,
					fade: false,
					slidesToShow: 1,
					slidesToScroll: 1,
					responsive: [
						{ breakpoint: 768 - 1,  settings: { slidesToShow: 2, slidesToScroll: 2 } },
						{ breakpoint: 992 - 1,  settings: { slidesToShow: 3, slidesToScroll: 3 } }
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

		
		wptheme.initCenterFinderBlocks = function() {
			$('.wp-block-crown-blocks-center-finder').each(function(i, el) {
				var block = $(el);

				wptheme.initMap($('.google-map', block));

				block.on('click', '.locations article.location', function(e) {
					var location = $(this);
					if(location.hasClass('active')) return;
					var block = $(this).closest('.wp-block-crown-blocks-center-finder');

					$('.locations article.location.active', block).removeClass('active');
					var preview = location.clone();
					location.addClass('active');
					$('.preview', block).html(preview);

					var locationId = parseInt(location.data('location-id'));
					var map = $('.map .google-map', block);
					if(map.hasClass('map-initialized')) {
						var mapData = map.data('map-data');
						for(var i in mapData.markers) mapData.markers[i].setIcon(mapSettings.markerImages.red);
						var marker = mapData.markers.find(function(n) { return n.data.locationId == locationId; });
						if(typeof marker !== 'undefined') {
							marker.setIcon(mapSettings.markerImages.blue);
						}
					} else {
						map.on('mapInitialized', function(e) {
							var mapData = $(this).data('map-data');
							for(var i in mapData.markers) mapData.markers[i].setIcon(mapSettings.markerImages.red);
							var marker = mapData.markers.find(function(n) { return n.data.locationId == locationId; });
							if(typeof marker !== 'undefined') {
								marker.setIcon(mapSettings.markerImages.blue);
							}
						});
					}
				});
				$('.locations article.location:first', block).trigger('click');

			});
		};


		wptheme.initSectionNavBlocks = function() {

			$('.wp-block-crown-blocks-section-nav').each(function(i, el) {
				var navBlock = $(el);
				var nav = $('<nav class="section-nav-nav"><div class="inner"><ul class="menu"></ul></div></nav>');
				$('.section-nav-contents', navBlock).before(nav);
				if($('.section-nav-title', navBlock).length) $('> .inner', nav).prepend('<h2>' + $('.section-nav-title', navBlock).text() + '</h2>');
				$('.wp-block-crown-blocks-section-nav-content', navBlock).each(function(j, el2) {
					var contentBlock = $(el2);
					var title = 'Section ' + (j + 1);
					if($('.section-nav-content-title', contentBlock).length) title = $('.section-nav-content-title', contentBlock).text();
					$('.menu', nav).append('<li><a href="#">' + title + '</a></li>');
				});
				$(window).trigger('load');
			});

			$(document).on('click', '.wp-block-crown-blocks-section-nav .section-nav-nav .menu a', function(e) {
				e.preventDefault();
				var navBlock = $(this).closest('.wp-block-crown-blocks-section-nav');
				var itemIndex = $(this).parent().index();
				if(itemIndex == 0) {
					var offset = 0;
					if(navBlock.hasClass('layout-sidebar')) offset = 32;
					wptheme.smoothScrollToElement(navBlock, 1000, -offset);
				} else {
					var contentBlock = $('.section-nav-contents > .inner > .wp-block-crown-blocks-section-nav-content', navBlock).eq(itemIndex);
					var offset = $('body').width() >= 768 ? $('.section-nav-nav', navBlock).outerHeight() : 0;
					if(navBlock.hasClass('layout-sidebar')) offset = 32;
					wptheme.smoothScrollToElement(contentBlock, 1000, -offset);
				}
			});

			var updateSectionNavBlocks = function() {
				var scrollTop = $(window).scrollTop();
				var windowHeight = $(window).height();
				$('.wp-block-crown-blocks-section-nav').each(function(i, el) {
					var block = $(el);
					var nav = $('> .inner > .section-nav-nav', block);
					var sections = $('> .inner > .section-nav-contents > .inner > .wp-block-crown-blocks-section-nav-content', block);
					var currentSection = sections.first();
					while(currentSection.next().length) {
						if(currentSection.next().offset().top < scrollTop + (windowHeight / 2)) {
							currentSection = currentSection.next();
						} else {
							break;
						}
					}
					var currentNavItem = $('.menu li:eq(' + currentSection.index() + ')', nav);
					if(!currentNavItem.hasClass('active')) {
						$('.menu li.active', nav).removeClass('active');
						currentNavItem.addClass('active');
					}
				});
			};
			if($('.wp-block-crown-blocks-section-nav').length) {
				updateSectionNavBlocks();
				$(window).on('load scroll resize', updateSectionNavBlocks);
			}

		};


		wptheme.initTeamMemberIndexBlocks = function() {

			$(document).on('click', '.wp-block-crown-blocks-team-member-index article.team_member > a', function(e) {
				e.preventDefault();
				var block = $(this).closest('.wp-block-crown-blocks-team-member-index');
				var article = $(this).closest('article');
				var container = article.parent();
				if(article.hasClass('active')) {
					article.removeClass('active');
					$('.drawer', container).each(function(i, el) {
						var oldDrawer = $(el);
						$('> .inner', oldDrawer).css({ height: oldDrawer.height() });
						oldDrawer.removeClass('active');
						setTimeout(function() { $('> .inner', oldDrawer).css({ height: 0 }) }, 100);
						setTimeout(function() { oldDrawer.remove(); }, 600);
					});
				} else {
					$('> article.active', container).removeClass('active');
					article.addClass('active');
					var scrollTo = $('.entry-teaser', article).offset().top;

					var lirArticle = article;
					while(lirArticle.nextAll('article').length && lirArticle.nextAll('article').first().offset().top == article.offset().top) {
						lirArticle = lirArticle.nextAll('article').first();
					}
					var drawer = lirArticle.next('.drawer');
					if(!drawer.length) {
						drawer = $('<div class="drawer"><div class="inner"></div></div>');
						lirArticle.after(drawer);
					}
					$('.drawer', container).not(drawer).each(function(i, el) {
						var oldDrawer = $(el);
						oldDrawer.hide();
						scrollTo = $('.entry-teaser', article).offset().top;
						oldDrawer.show();
						$('> .inner', oldDrawer).css({ height: oldDrawer.height() });
						oldDrawer.removeClass('active');
						setTimeout(function() { $('> .inner', oldDrawer).css({ height: 0 }) }, 100);
						setTimeout(function() { oldDrawer.remove(); }, 500);
					});

					article.addClass('loading');
					$.get(crownThemeData.ajaxUrl, { action: 'get_block_team_member_index_member_details', id: $(this).data('post-id') }, function(response) {
						var article = $('.wp-block-crown-blocks-team-member-index article.active.post-' + response.id);
						if(article.length) {
							var drawer = article.nextAll('.drawer').first();
							if(response.content && drawer.length) {
								article.removeClass('loading');
								var drawerContent = $(response.content);
								$('> .inner', drawerContent).prepend('<button type="button" class="drawer-close">Close</button>');
								$('> .inner', drawer).css({ height: drawer.hasClass('active') ? drawer.height() : 0 });
								$('> .inner', drawer).html(drawerContent);
								drawer.addClass('active');
								setTimeout(function() { $('> .inner', drawer).css({ height: drawerContent.outerHeight() }) }, 100);
								setTimeout(function() { $('> .inner', drawer).css({ height: 'auto' }) }, 600);
								// if(article.offset().left >= Math.floor($('body').width() / 2)) {
								// 	drawerContent.addClass('flipped');
								// }
								var colors = [ 'dark-blue', 'blue', 'red', 'gray', 'dark-gray' ];
								drawerContent.attr('data-color', colors[Math.floor(Math.random() * Math.floor(colors.length))]);
							}
						}
					}, 'json');

					wptheme.smoothScrollToPos(scrollTo, 500, -32);
					
					// $('> .inner', drawer).css({ height: drawer.hasClass('active') ? drawer.height() : 0 });
					// $('> .inner', drawer).html(drawerContent);
					// drawer.addClass('active');
					// setTimeout(function() { $('> .inner', drawer).css({ height: drawerContent.outerHeight() }) }, 100);
					// setTimeout(function() { $('> .inner', drawer).css({ height: 'auto' }) }, 600);
				}
			});

			$(document).on('click', '.wp-block-crown-blocks-team-member-index .post-feed .drawer button.drawer-close', function(e) {
				var oldDrawer = $(this).closest('.drawer');
				oldDrawer.siblings('article.active').removeClass('active');
				$('> .inner', oldDrawer).css({ height: oldDrawer.height() });
				oldDrawer.removeClass('active');
				setTimeout(function() { $('> .inner', oldDrawer).css({ height: 0 }) }, 100);
				setTimeout(function() { oldDrawer.remove(); }, 600);
			});

			$(window).on('resize', function() {
				$('.wp-block-crown-blocks-team-member-index .post-feed .drawer').each(function(i, el) {
					var drawer = $(el);
					var container = drawer.parent();
					var article = $('> article.active', container);
					drawer.hide();
					var lirArticle = article;
					while(lirArticle.nextAll('article').length && lirArticle.nextAll('article').first().offset().top == article.offset().top) {
						lirArticle = lirArticle.nextAll('article').first();
					}
					if(!lirArticle.next('.drawer').length || !lirArticle.next('.drawer').is(drawer)) {
						lirArticle.after(drawer);
					}
					drawer.show();
					var drawerContent = $('> .inner', drawer).children().first();
					if(article.offset().left == article.parent().offset().left) {
						drawerContent.addClass('flipped');
					} else {
						drawerContent.removeClass('flipped');
					}
				});
			});

		};


		wptheme.initBranchMapShortcodes = function() {
			$('.branch-map > .google-map').each(function(i, el) {
				wptheme.initMap($(el));
			});
		};

		
		wptheme.initHeaders = function() {
			$('.wp-block-crown-blocks-header .hr-container').each(function(i, el) {
				$(el).addClass('reveal-right');
			});
			var animateHeaderHrs = function() {
				var scrollTop = $(window).scrollTop();
				var windowHeight = $(window).height();
				$('.wp-block-crown-blocks-header .hr-container.reveal-right:not(.animated)').each(function(i, el) {
					if($(el).offset().top <= scrollTop + (windowHeight * .9)) {
						var container = $(el);
						var hr = $('hr', container);
						container.css({ width: '100%' });
						hr.css({ width: hr.width() });
						container.css({ width: 0 });
						setTimeout(function() { container.addClass('animated'); }, 0);
						setTimeout(function() { container.css({ width: '100%' }); }, 00);
						setTimeout(function() { hr.css({ width: '100%' }); }, 500);
					}
				});
			};
			animateHeaderHrs();
			$(window).on('load scroll', animateHeaderHrs);
		};


		wptheme.initOdometers = function() {

			$('.odometer-statistic').each(function(i, el) {
				var value = $('.stat-value', el);
				$(el).addClass('fade-in');
				if(value.length && (matches = value.text().match(/([^\d]*)([\d\.,]+)(.*)/))) {

					value.data('odometer-final-value', matches[2]);

					var preOd = $('<span class="pre">' + matches[1] + '</span>');
					var odometer = $('<span class="odometer">' + matches[2] + '</span>');
					var postOd = $('<span class="post">' + matches[3] + '</span>');
					value.html('');
					value.append(preOd).append(odometer).append(postOd);
					
					var od = new Odometer({ el: odometer[0], value: '0'.padStart(5, 0) });

				}
			});

			var animateStats = function() {
				var scrollTop = $(window).scrollTop();
				var windowHeight = $(window).height();
				$('.odometer-statistic.fade-in:not(.animated)').each(function(i, el) {
					var value = $('.stat-value', el);
					if(value.offset().top <= scrollTop + (windowHeight * .9)) {
						$(el).addClass('animated');
						$('.odometer', value).text(value.data('odometer-final-value'));
					}
				});
			};
			animateStats();
			$(window).on('load scroll', animateStats);

		};


		wptheme.smoothScrollToElement = function(element, speed, offset) {
			speed = typeof speed !== 'undefined' ? speed : 1000;
			offset = typeof offset !== 'undefined' ? offset : 0;
			if(element.length > 0) {
				var margin = parseInt(element.css('margin-top'));
				wptheme.smoothScrollToPos(element.offset().top - (margin > 0 ? margin : 0), speed, offset);
			}
		};
		wptheme.smoothScrollToPos = function(y, speed, offset) {
			speed = typeof speed !== 'undefined' ? speed : 1000;
			offset = typeof offset !== 'undefined' ? offset : 0;
			var windowWidth = $('body').width();
			var fixedHeaderOffset = windowWidth > 600 && $('#wpadminbar').length ? $('#wpadminbar').outerHeight() : 0;
			keepStickyHeaderHidden = true;
			$('#header').addClass('is-minified');
			$('html, body').stop(true).animate({ scrollTop: y - fixedHeaderOffset + offset }, speed, 'easeOutExpo');
			setTimeout(function() { keepStickyHeaderHidden = false; }, speed);
		};


		return wptheme;
		
	})({});

})(jQuery);



function getQueryStringValue(key) {  
	return unescape(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + escape(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));  
}

function setCookie(name, value, days, path) {
	path = typeof path !== 'undefined' ? path : '/';
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "")  + expires + "; path=" + path;
}
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function padNumber(number, length) {
	var str = '' + number;
	while(str.length < length) str = '0' + str;
	return str;
}