<?php

namespace SB\SocialWall\Core\Abstracts;

use DI\NotFoundException;
use DI\DependencyException;
use SB\SocialWall\SocialWall;
use SB\SocialWall\Core\Interfaces\ProviderInterface;

/**
 * Handles instantiation of services.
 */
abstract class Provider implements ProviderInterface {

	/**
	 * Service provider.
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Returns all the services that should be instantiated.
	 *
	 * @return array
	 */
	abstract protected function services();

	/**
	 * Checks if a providers' service should be registered.
	 *
	 * @return bool
	 */
	abstract protected function can_be_registered();

	/**
	 * Registers services with the container.
	 *
	 * @return void
	 */
	public function register() {
		if ( ! $this->can_be_registered() ) {
			return;
		}

		if ( ! is_array( $this->services() ) || empty( $this->services() ) ) {
			return;
		}

		foreach ( $this->services() as $service ) {
			if ( ! class_exists( $service ) ) {
				continue;
			}

			$service = new $service;
			if ( $service instanceof Service ) {
				$service->register_hooks();
			}
		}
	}
}
