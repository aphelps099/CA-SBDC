<?php

if ( ! class_exists( 'Crown_Theme_Walker_Nav_Menu_Mobile_Menu' ) && class_exists( 'Walker_Nav_Menu' ) ) {
	class Crown_Theme_Walker_Nav_Menu_Mobile_Menu extends Walker_Nav_Menu {


		public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
			if ( ! $element ) {
				return;
			}
	
			$id_field = $this->db_fields['id'];
			$id       = $element->$id_field;
	
			$this->has_children = ! empty( $children_elements[ $id ] );
			if ( isset( $args[0] ) && is_array( $args[0] ) ) {
				$args[0]['has_children'] = $this->has_children;
			}
	
			$this->start_el( $output, $element, $depth, ...array_values( $args ) );
	
			if ( ( 0 == $max_depth || $max_depth > $depth + 1 ) && isset( $children_elements[ $id ] ) ) {
	
				foreach ( $children_elements[ $id ] as $child ) {
	
					if ( ! isset( $newlevel ) ) {
						$newlevel = true;
						$output .= '<button type="button" class="sub-menu-toggle">' . ct_get_icon( 'chevron-right' ) . '</button>';
						$output .= '<div class="menu-container sub-menu-container"><div class="inner">';
						if ( $depth == 0 & ! empty( $element->description ) ) {
							$output .= '<h6 class="sub-menu-title">' . $element->description . '</h6>';
						}
						$this->start_lvl( $output, $depth, ...array_values( $args ) );
					}
					$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
				}
				unset( $children_elements[ $id ] );
			}
	
			if ( isset( $newlevel ) && $newlevel ) {
				$this->end_lvl( $output, $depth, ...array_values( $args ) );
				$output .= '</div></div>';
			}
	
			$this->end_el( $output, $element, $depth, ...array_values( $args ) );
		}


	}
}