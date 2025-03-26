<?php
/**
 * Class SB_Instagram_Parse
 *
 * The structure of the data coming from the Instagram API is different
 * for the old API vs the new graph API. This class is used to parse
 * whatever structure the data has as well as use this to generate
 * parts of the html used for image sources.
 *
 * @since 2.0/5.0
 */
use TwitterFeed\Pro\CTF_Parse_Pro;
use SmashBalloon\YouTubeFeed\Pro\SBY_Parse_Pro;
use SmashBalloon\TikTokFeeds\Common\FeedParse as SBTT_Parse_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SW_Parse
{
	/**
	 * Used to append data to a post before it's displayed.
	 *
	 * @param array $post The data for the individual social media post.
	 *
	 * @return array
	 */
	public static function filter_post($post)
	{
		$type = self::get_plugin($post);

		if ($type === 'twitter') {
			$maybe_youtube = self::maybe_parse_youtube($post);

			if (! empty($maybe_youtube)) {
				if ($post['twitter_card']) {
					$post['twitter_card']['twitter:image'] = $maybe_youtube['video_thumb'];
				}
			}
		}
		return $post;
	}

	/**
	 * Parses a Twitter post for YouTube related data
	 *
	 * @param array $post The data for the individual social media post.
	 *
	 * @return array
	 */
	public static function maybe_parse_youtube($post)
	{
		$iframe_data = array();
		if (! empty($post['entities']['urls'][0]['expanded_url'])) {
			$iframe_data = CTF_Parse_Pro::iframe_data($post['entities']['urls'][0]['expanded_url']);
			$maybe_id = false;
			if (strpos($post['entities']['urls'][0]['expanded_url'], 'youtube.com/shorts/') !== false) {
				$end_url = str_replace('https://youtube.com/shorts/', '', $post['entities']['urls'][0]['expanded_url']);
				$maybe_id = explode('?', $end_url)[0];
			} elseif (
				! empty($iframe_data)
				&& strpos($iframe_data['url'], 'https://www.youtube.com/embed/') !== false
			) {
				$maybe_id = str_replace('https://www.youtube.com/embed/', '', $iframe_data['url']);
			}

			if ($maybe_id) {
				$iframe_data['video_id']    = $maybe_id;
				$iframe_data['video_thumb'] = 'https://img.youtube.com/vi/' . $maybe_id . '/mqdefault.jpg';
			}
		}

		return $iframe_data;
	}

	public static function get_plugin( $post ) {

		if ( is_object( $post )
		     || isset( $post['message'] )
		     || isset( $post['full_picture'] )
		     || isset( $post['privacy'] )
			 || isset( $post['cover'] )
		     || isset( $post['owner'] )
		     || isset( $post['picture'] )
			 || (isset($post['embed_html']) && !isset($post['open_id']))
		     || isset( $post['cover_photo'] ) ) {
			return 'facebook';
		} else if ( isset( $post['snippet'] ) ) {
			return 'youtube';
		} elseif ( isset( $post['permalink'] ) ) {
			return 'instagram';
		} elseif ( isset( $post['id_str'] ) ) {
			return 'twitter';
		} elseif (isset($post['embed_link']) && isset($post['open_id'])) {
			return 'tiktok';
		} else {
			return 'facebook';
		}
	}

	public static function get_username( $data, $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $data );
		}

		$username = '';
		if ( $plugin === 'youtube' ) {
			$username = SBY_Parse_Pro::get_channel_title( $data );
		} elseif ( $plugin === 'instagram' ) {
			$username = SB_Instagram_Parse_Pro::get_username( $data );
		} elseif ( $plugin === 'facebook' ) {
			$username = CustomFacebookFeed\CFF_Parse_Pro::get_name( $data );
		} elseif ( $plugin === 'twitter' ) {
			$username = CTF_Parse_Pro::get_handle( $data );
		}

		return $username;
	}

	public static function get_media_type( $post, $settings = array(), $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		if ( $plugin === 'youtube' ) {
			return 'youtube';
		} elseif ( $plugin === 'instagram' ) {
			return SB_Instagram_Parse_Pro::get_media_type( $post );
		} elseif ( $plugin === 'facebook' ) {
			$attachments = CustomFacebookFeed\CFF_Parse_Pro::get_attachments( $post );
			if ( ! empty( $attachments ) ) {
				$type = CustomFacebookFeed\CFF_Parse_Pro::get_attachment_media_type( $attachments[0] );
				if ( $type === 'video' ) {
					return 'iframe';
				}
				return $type;
			} elseif ( ! empty( CustomFacebookFeed\CFF_Parse_Pro::get_iframe_html( $post ) ) ) {
				return 'iframe';
			}
			return 'image';
		} elseif ( $plugin === 'twitter' ) {
			return CTF_Parse_Pro::get_media_type( $post );
		} elseif ($plugin === 'tiktok') {
			return 'video';
		}
		return '';
	}

	public static function get_media_thumbnail( $post, $settings = array(), $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$thumbnail_url = '';
		if ( $plugin === 'youtube' ) {
			$thumbnail_url = SBY_Parse_Pro::get_media_url( $post, 'lightbox' );
		} elseif ( $plugin === 'instagram' ) {
			$thumbnail_url = SB_Instagram_Parse_Pro::get_media_url( $post, 'lightbox' );
		} elseif ( $plugin === 'facebook' ) {
			$thumbnail_url = CustomFacebookFeed\CFF_Parse_Pro::get_media_url( $post, 'lightbox' );
		} elseif ( $plugin === 'twitter' ) {
			$thumbnail_url = CTF_Parse_Pro::get_media_url( $post, 'lightbox' );
		} elseif ($plugin === 'tiktok') {
			$thumbnail_url = SBTT_Parse_Pro::get_cover_image_url($post);
		}

		return $thumbnail_url;
	}

	public static function get_video_url( $post, $plugin ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$video_url = '';
		if ( $plugin === 'youtube' ) {
			$video_url = SBY_Parse_Pro::get_media_url( $post, 'lightbox' );
		} elseif ( $plugin === 'instagram' ) {
			$video_url = '';
			$lb_atts = SB_Instagram_Parse_Pro::get_lightbox_media_atts( $post );
			if ( ! empty( $lb_atts['video'] ) ) {
				$video_url = $lb_atts['video'];
			}
		} elseif ( $plugin === 'facebook' ) {
			$video_url = CustomFacebookFeed\CFF_Parse_Pro::get_media_url( $post, 'lightbox' );

		} elseif ( $plugin === 'twitter' ) {
			$video_url = CTF_Parse_Pro::get_video_url( $post );
		} elseif ($plugin === 'tiktok') {
			if (method_exists(SBTT_Parse_Pro::class, 'get_post_video_url')) {
				$video_url = SBTT_Parse_Pro::get_post_video_url($post);
			}
		}

		return $video_url;
	}

	public static function get_iframe_url( $post, $plugin ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		if ( $plugin === 'youtube' ) {
			return '';
		} elseif ( $plugin === 'instagram' ) {
			return '';
		} elseif ( $plugin === 'facebook' ) {
			$attachments = CustomFacebookFeed\CFF_Parse_Pro::get_attachments( $post );
			if ( ! empty( $attachments ) ) {
				$video_url = CustomFacebookFeed\CFF_Parse_Pro::get_attachment_unshimmed_url( $attachments[0] );
				return 'https://www.facebook.com/v2.3/plugins/video.php?href='.$video_url;
			} else {
				$iframe_html = CustomFacebookFeed\CFF_Parse_Pro::get_iframe_html( $post );

				if ( ! empty( $iframe_html ) ) {
					$iframeSrcMatches = [];
					preg_match('/(?<=src=").*(?=")/', $iframe_html, $iframeSrcMatches);
					return $iframeSrcMatches[0];
				}
			}
			return '';
		} elseif ( $plugin === 'twitter' ) {
			return '';
		} else {
			return '';
		}
	}

	public static function get_account_identifier( $post, $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$identifier = '';
		if ( $plugin === 'youtube' ) {
			$identifier = SBY_Parse_Pro::get_channel_id( $post );
		} elseif ( $plugin === 'instagram' ) {
			$identifier = SB_Instagram_Parse_Pro::get_username( $post );
		} elseif ( $plugin === 'facebook' ) {
			$identifier = CustomFacebookFeed\CFF_Parse_Pro::get_name( $post );
		} elseif ( $plugin === 'twitter' ) {
			$identifier = CTF_Parse_Pro::get_handle( $post );
		}

		return $identifier;
	}

	public static function get_full_name( $data, $post, $plugin = '' ) {

		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$identifier = SW_Parse::get_account_identifier( $post, $plugin );

		$name = '';
		if ( $plugin === 'youtube' ) {
			if ( isset( $data[ $plugin ][ $identifier ] ) ) {
				$name = SBY_Parse_Pro::get_channel_title( $data[ $plugin ][ $identifier ] );
			}
		} elseif ( $plugin === 'instagram' ) {
			if ( isset( $data[ $plugin ][ $identifier ] ) ) {
				$name = SB_Instagram_Parse_Pro::get_name( $data[ $plugin ][ $identifier ] );
			}
		} elseif ( $plugin === 'facebook' ) {
			$name = CustomFacebookFeed\CFF_Parse_Pro::get_name( $post );
		} elseif ( $plugin === 'twitter' ) {
			$name = CTF_Parse_Pro::get_name( $post );
		} elseif ($plugin === 'tiktok') {
			if (isset($data['tiktok'])) {
				$source = self::get_matching_source($data['tiktok'], $post);
				$name = SBTT_Parse_Pro::get_username($source);
			}
		}

		return $name;
	}

	public static function get_avatar( $data, $post, $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$identifier = SW_Parse::get_account_identifier( $post, $plugin );
		$avatar = '';
		if ( $plugin === 'youtube' ) {
			if ( isset( $data[ $plugin ][ $identifier ] ) ) {
				$avatar = SBY_Parse_Pro::get_avatar( $data[ $plugin ][ $identifier ] );
			}
		} elseif ( $plugin === 'instagram' ) {
			if ( isset( $data[ $plugin ][ $identifier ] ) ) {
				$avatar = SB_Instagram_Parse_Pro::get_avatar( $data[ $plugin ][ $identifier ] );
			}
		} elseif ( $plugin === 'facebook' ) {
			$avatar = CustomFacebookFeed\CFF_Parse_Pro::get_avatar( $post );
		} elseif ( $plugin === 'twitter' ) {
			$avatar = CTF_Parse_Pro::get_avatar( $post );
		} elseif ($plugin === 'tiktok') {
			if (isset($data['tiktok'])) {
				$source = self::get_matching_source($data['tiktok'], $post);
				$avatar = SBTT_Parse_Pro::get_avatar_url($source);
			}
		}

		return $avatar;
	}

	public static function get_description( $post, $plugin = '', $default = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$caption = '';
		if ( $plugin === 'youtube' ) {
			$caption = SBY_Parse_Pro::get_caption( $post, $default );
		} elseif ( $plugin === 'instagram' ) {
			$caption = SB_Instagram_Parse_Pro::get_caption( $post, $default );
		} elseif ( $plugin === 'facebook' ) {
			$caption = CustomFacebookFeed\CFF_Parse_Pro::get_message( $post );
		} elseif ( $plugin === 'twitter' ) {
			$caption = CTF_Parse_Pro::get_tweet_content( $post, $default = '' );
		} elseif ($plugin === 'tiktok') {
			$caption = SBTT_Parse_Pro::get_video_description($post);
		}

		return $caption;
	}

	public static function get_post_title( $post, $plugin = '', $default = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$post_title = '';
		if ( $plugin === 'youtube' ) {
			$post_title = SBY_Parse_Pro::get_video_title( $post, $default );
		} elseif ( $plugin === 'instagram' ) {
			$post_title = '';
		} elseif ( $plugin === 'facebook' ) {
			$post_title = '';
		} elseif ( $plugin === 'twitter' ) {
			$post_title = '';
		}

		return $post_title;
	}

	public static function get_post_permalink( $post, $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$permalink = '';
		if ( $plugin === 'youtube' ) {
			$permalink = SBY_Parse_Pro::get_permalink( $post );
		} elseif ( $plugin === 'instagram' ) {
			$permalink = SB_Instagram_Parse_Pro::get_permalink( $post );
		} elseif ( $plugin === 'facebook' ) {
			$permalink = CustomFacebookFeed\CFF_Parse_Pro::get_permalink( $post );
		} elseif ( $plugin === 'twitter' ) {
			$permalink = CTF_Parse_Pro::get_permalink( $post );
		} elseif ($plugin === 'tiktok') {
			$permalink = SBTT_Parse_Pro::get_post_url($post);
		}

		return $permalink;
	}

	public static function get_account_link( $account_data, $post, $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$permalink = '';
		if ( $plugin === 'youtube' ) {
			$permalink = SBY_Parse_Pro::get_channel_permalink( $post );
		} elseif ( $plugin === 'instagram' ) {
			$username = SB_Instagram_Parse_Pro::get_username( $post );
			if ( ! empty( $username ) ) {
				$permalink = 'https://www.instagram.com/' . SB_Instagram_Parse_Pro::get_username( $post ) . '/';
			} else {
				$permalink = 'https://www.instagram.com/' . SB_Instagram_Parse_Pro::get_instagram_url_part( $account_data['instagram'], $post ) . '/';
			}
		} elseif ( $plugin === 'facebook' ) {
			if ( CustomFacebookFeed\CFF_Parse_Pro::get_status_type( $post ) === 'event'
			     || CustomFacebookFeed\CFF_Parse_Pro::get_status_type( $post ) === 'photo'
			     || CustomFacebookFeed\CFF_Parse_Pro::get_status_type( $post ) === 'album' ) {
				$permalink = CustomFacebookFeed\CFF_Parse_Pro::get_permalink( $post );
			} else {
				$permalink = CustomFacebookFeed\CFF_Parse_Pro::get_from_link( $post );
			}
		} elseif ( $plugin === 'twitter' ) {
			$permalink = CTF_Parse_Pro::get_account_link( $post );
		} elseif ($plugin === 'tiktok') {
			if (isset($account_data['tiktok'])) {
				$source = self::get_matching_source($account_data['tiktok'], $post);
				$permalink = SBTT_Parse_Pro::get_profile_url($source);
			}
		}

		return $permalink;
	}

	/**
	 * @param $post array
	 *
	 * @return mixed
	 *
	 * @since 2.0/5.0
	 */
	public static function get_post_id( $post, $plugin ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}
		if ( $plugin === 'youtube' ) {
			return SBY_Parse_Pro::get_video_id( $post );
		} elseif ( $plugin === 'instagram' ) {
			return SB_Instagram_Parse_Pro::get_post_id( $post );
		} elseif ( $plugin === 'facebook' ) {
			return CustomFacebookFeed\CFF_Parse_Pro::get_post_id( $post );
		} elseif ( $plugin === 'twitter' ) {
			return CTF_Parse_Pro::get_tweet_id( $post );
		} elseif ($plugin === 'tiktok') {
			return SBTT_Parse_Pro::get_post_id($post);
		}

		return 'missing';
	}

	/**
	 * @param $post array
	 *
	 * @return false|int
	 *
	 * @since 2.0/5.0
	 */
	public static function get_timestamp( $post, $plugin = '' ) {
		if ( empty( $plugin ) ) {
			$plugin = SW_Parse::get_plugin( $post );
		}

		$timestamp = '';
		if ( $plugin === 'youtube' ) {
			$timestamp = SBY_Parse_Pro::get_timestamp( $post );
		} elseif ( $plugin === 'instagram' ) {
			$timestamp = SB_Instagram_Parse_Pro::get_timestamp( $post );
		} elseif ( $plugin === 'facebook' ) {
			$timestamp = CustomFacebookFeed\CFF_Parse_Pro::get_timestamp( $post );
		} elseif ( $plugin === 'twitter' ) {
			$timestamp = CTF_Parse_Pro::get_timestamp( $post );
		} elseif ($plugin === 'tiktok') {
			$timestamp = SBTT_Parse_Pro::get_post_create_time($post);
		}

		return $timestamp;
	}

	/**
	 * Function to get the source of the post
	 * 
	 * @param $source array Source data
	 * @param $post array Post data
	 * 
	 * @return array
	 */
	public static function get_matching_source($source, $post)
	{
		if (!empty($source[0]) && is_array($source[0])) {
			$matching_source = array_filter($source, function ($source) use ($post) {
				return $source['open_id'] === $post['open_id'];
			});
			return array_shift($matching_source);
		}
		return $source;
	}
}
