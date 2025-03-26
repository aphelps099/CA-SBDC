<?php
/**
 * Class UserTimelineManager
 *
 * @since 1.0
 */
namespace TwitterFeed\SmashTwitter;
class UserTimelineManager
{

	const USERS_TIMLINE_OPTIONS = 'ctf_timeline_users';

	/**
	 * Get List Of Timline Users
	 *
	 * @return array
	 *
	 * @since XX
	 */
	public static function get_users()
	{
		$list = get_option(self::USERS_TIMLINE_OPTIONS, []);
		return is_array($list) ? $list : [];
	}

	/**
	 * Get User ID
	 *
	 * @return string|boolean
	 *
	 * @since XX
	 */
	public static function get_user_id($screen_name)
	{
		$users_list = self::get_users();
		if (empty($screen_name)) {
			return false;
		}
		$screen_name = str_replace('@', '', $screen_name);

		//Check if user exist
		$user_index = array_search(
			$screen_name,
			array_column($users_list, 'screen_name')
		);

		return false !== $user_index ? $users_list[$user_index]['rest_id'] : false;
	}

	/**
	 * Add New or Update User Timeline
	 * Check if user exists then we updated (UserName)
	 * else we add it!
	 *
	 * @return array
	 *
	 * @since XX
	 */
	public static function add_update_user($user = [])
	{
		$users_list = self::get_users();
		if (!empty($user['screen_name']) && !empty($user['rest_id'])) {
			//Check if user exist
			$user_index = array_search(
				$user['rest_id'],
				array_column($users_list, 'rest_id')
			);
			if (false !== $user_index) {
				$users_list[$user_index] = $user;
			} else {
				array_push(
					$users_list,
					$user
				);
			}

			$unique_array = array_unique(
				array_column($users_list, 'rest_id')
			);
			$new_array = array_intersect_key($users_list, $unique_array);
			update_option(self::USERS_TIMLINE_OPTIONS, $new_array);
		}
		return $users_list;
	}

	/**
	 * Delete User from the list
	 *
	 * @return array
	 *
	 * @since XX
	 */
	public static function delete_user($user = [])
	{
		$users_list = self::get_users();
		if (!empty($user['screen_name']) && !empty($user['rest_id'])) {
			$user_index = array_search(
				$user['rest_id'],
				array_column($users_list, 'rest_id')
			);
			if (false !== $user_index) {
				unset($users_list[$user_index]);
				update_option(self::USERS_TIMLINE_OPTIONS, $users_list);
			}
		}
		return $users_list;
	}


}