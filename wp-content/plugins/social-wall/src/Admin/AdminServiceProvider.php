<?php

namespace SB\SocialWall\Admin;

use SB\SocialWall\Admin\Services\Ajax;
use SB\SocialWall\Admin\Services\Asset;
use SB\SocialWall\Admin\Services\Tooltip;
use SB\SocialWall\Admin\Services\Block;
use SB\SocialWall\Core\Abstracts\Provider;

class AdminServiceProvider extends Provider {

	/**
	 * Returns all the services that should be instantiated.
	 *
	 * @return array
	 */
	protected function services() {
		return [
			Asset::class,
			Ajax::class,
			Tooltip::class,
		];
	}

	/**
	 * Checks if a providers' service should be registered.
	 *
	 * @return bool
	 */
	protected function can_be_registered() {
		return is_admin();
	}
}
