<?php
/**
 * SBY_Live_Streams.
 *
 * Live streamed videos are stored in a cache due to there being no
 * reliable way to retrieve then using an API key and a standard API endpoint.
 * The RSS feed for the channel is used to get the latest 15 published videos
 * and this class can be used tp store all of the ones that are scheduled
 * live streams.
 *
 * @since 1.3
 */

namespace SmashBalloon\YouTubeFeed\Pro;

use SmashBalloon\YouTubeFeed\SBY_Parse;
use SmashBalloon\YouTubeFeed\SBY_RSS_Connect;

class SBY_Live_Streams {

	/**
	 * @var string
	 */
	private $channel;

	/**
	 * @var string
	 */
	private $cache_name;

	/**
	 * @var array
	 */
	private $video_cache;

	/**
	 * SBY_Live_Streams constructor.
	 *
	 * @param $channel string
	 *
	 * @since 1.3
	 */
	public function __construct( $channel ) {
		$this->channel = $channel;
		$this->cache_name = 'sby_livestreams_' . $channel;
		$this->video_cache = get_option( $this->cache_name, array() );
	}

	/**
	 * Cached video api data
	 *
	 * @return array
	 *
	 * @since 1.3
	 */
	public function get_video_cache() {
		return $this->video_cache;
	}

	/**
	 * Get latest 15 videos from RSS and return only
	 * live streams. Use the video IDs to retrieve details
	 * using the "single" video API endpoint. Update or add
	 * to the cache based on results.
	 *
	 * @return array
	 *
	 * @since 1.3
	 */
	public function add_remote_posts() {
		$rss_videos = $this->fetch_rss();
		$live_streams = $this->fetch_singles( $rss_videos );
		$this->update_or_add( $live_streams );

		return $live_streams;
	}

	/**
	 * Refresh the API data for the most recent 40
	 * videos in the cache. Can exclude videos if they
	 * were just updated from another process.
	 *
	 * @param $exclude array
	 *
	 * @since 1.3
	 */
	public function update_cached_video_details( $exclude ) {
		$to_check = array_slice( $this->video_cache, 0, 40 + count( $exclude ) );

		$to_update = array();
		foreach ( $to_check as $vid_id => $post ) {
			// $exclude set as an associative array with the video ID
			// as the index.
			if ( ! isset( $exclude[ $vid_id ] )
			     && count( $to_update ) < 40 ) {
				$to_update[] = $vid_id;
			}
		}


		if ( ! empty( $to_update ) ) {
			$live_streams = $this->fetch_singles( $to_update );
			$this->update_or_add( $live_streams, $to_update );
		}
	}

	/**
	 * The RSS feed contains live stream videos.
	 *
	 * @return array
	 *
	 * @since 1.3
	 */
	public function fetch_rss() {
		$params = array(
			'channel_id' => $this->channel,
			'livestream' => '1'
		);

		$connection = new SBY_RSS_Connect( 'playlistItems', $params );
		$connection->connect();
		$data = $connection->get_data();

		$ids = array();
		if ( is_array( $data ) ) {
			foreach ( $data as $post ) {
				$vid_id = SBY_Parse::get_video_id( $post );
				$ids[]  = $vid_id;
			}
		}

		return $ids;
	}

	/**
	 * Uses the "single" API endpoint to get video details and
	 * returns only those that appear to be live streams.
	 *
	 * @param $vid_ids array
	 *
	 * @return array
	 *
	 * @since 1.3
	 */
	public function fetch_singles( $vid_ids ) {
		$params['video_ids'] = $vid_ids;

		$connected_account = sby_get_first_connected_account();
		$video_connection =  new SBY_API_Connect_Pro( $connected_account, 'single', $params );

		$video_connection->connect();
		$potential_live_streams = $video_connection->get_data();
		$live_streamed_videos = array();
		if ( is_array( $potential_live_streams['items'] ) ) {
			foreach ( $potential_live_streams['items'] as $post ) {
				if ( ! empty( $post['liveStreamingDetails']['scheduledStartTime'] )
					|| ! empty( $post['liveStreamingDetails']['actualStartTime'] ) ) {
					$vid_id = SBY_Parse::get_video_id( $post );

					$live_streamed_videos[ $vid_id ] = $post;
				}
			}
		}

		return $live_streamed_videos;
	}

	/**
	 * Updates or adds to video cache stored as an associative array with
	 * the video ID as the index.
	 *
	 * @param $live_streams array
	 * @param $update_attempted array
	 *
	 * @since 1.3
	 */
	public function update_or_add( $live_streams, $update_attempted = array() ) {

		$actually_retrieved = array();
		foreach ( $live_streams as $vid_id => $live_stream ) {
			$actually_retrieved[] = $vid_id;
			$this->video_cache[ $vid_id ] = $live_stream;
		}

		// If a video ID is attempted to be retrieve but nothing is returned for it
		// It's likely that it was removed so we should remove it from the cache
		$no_longer_exist = array_diff( $update_attempted, $actually_retrieved );

		if ( ! empty( $no_longer_exist ) ) {
			foreach ( $no_longer_exist as $vid_id ) {
				if ( isset( $this->video_cache[ $vid_id ] ) ) {
					unset( $this->video_cache[ $vid_id ] );
				}
			}
		}
	}

	/**
	 * Orders videos by schedules start date or actual start date
	 * when available starting with the most recent.
	 *
	 * @since 1.3
	 */
	public function sort() {
		$post_set = $this->video_cache;
		uasort($post_set, 'sby_scheduled_start_sort' );
		$this->video_cache = $post_set;
	}

	/**
	 * Save most recent 200 videos in cache.
	 *
	 * @since 1.3
	 */
	public function update_cache() {
		$to_cache = array_slice( $this->video_cache, 0, 200 );

		update_option( $this->cache_name, $to_cache, false );
	}
}