<?php

if(!class_exists('Crown_Block_Nav_Menu')) {
	class Crown_Block_Nav_Menu extends Crown_Block {


		public static $name = 'nav-menu';


	}
	Crown_Block_Nav_Menu::init();
}