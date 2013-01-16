<?php
/**
 * Class containing functions required WordPress administration panels. Metabox on post/page edit views and options section under settings->media.
 *
 * @author ahoereth
 * @version 2013/01/09
 * @see ../featured_video_plus.php
 * @see featured_video_plus in general.php
 * @since 1.0
 *
 * @param featured_video_plus instance
 */
class featured_video_plus_backend {
	private $featured_video_plus;
	private $default_value;
	private $default_value_sec;
	private $help_localmedia;
	private $help_urls;

	/**
	 * Creates a new instace of this class, saves the featured_video_instance and default value for the meta box input.
	 *
	 * @since 1.0
	 *
	 * @param featured_video_plus_instance required, dies without
	 */
	function __construct( $featured_video_plus_instance ){
		if ( !isset($featured_video_plus_instance) )
			wp_die( 'featured_video_plus general instance required!', 'Error!' );

		$this->featured_video_plus 	= $featured_video_plus_instance;
		$this->default_value 		= __('Video URL', 'featured-video-plus');
		$this->default_value_sec 	= __('Fallback: same video, different format', 'featured-video-plus');
	}

	/**
	 * Enqueue all scripts and styles needed when viewing the backend.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://ottopress.com/2010/passing-parameters-from-php-to-javascripts-in-plugins/
	 * @see http://codex.wordpress.org/Function_Reference/wp_localize_script
	 * @since 1.0
	 */
	public function enqueue($hook_suffix) {
		// just required on options-media.php and would produce errors pre WordPress 3.1
		if( ($hook_suffix == 'options-media.php') && (get_bloginfo('version') >= 3.1) ) {
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) {
				// >=WP3.5
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'fvp_backend_35', FVP_URL . 'js/backend_35.js', array( 'wp-color-picker', 'jquery' ), FVP_VERSION );
			} else {
				// <WP3.5, fallback for the new WordPress Color Picker which was added in 3.5
				wp_enqueue_style( 'farbtastic' );
				wp_enqueue_script( 'farbtastic' );
				wp_enqueue_script( 'fvp_backend_pre35', FVP_URL . 'js/backend_pre35.js', array( 'jquery' ), FVP_VERSION );
			}
			wp_enqueue_script( 'fvp_backend_settings', FVP_URL . 'js/backend_settings.js', array( 'jquery' ), FVP_VERSION );
		}

		// just required on post.php
		if($hook_suffix == 'post.php' && isset($_GET['post']) ) {
			wp_enqueue_script( 'jquery.autosize', FVP_URL . 'js/jquery.autosize-min.js', array( 'jquery' ), FVP_VERSION );
			wp_enqueue_script( 'fvp_backend', FVP_URL . 'js/backend-min.js', array( 'jquery','jquery.autosize' ), FVP_VERSION ); 	// production
			//wp_enqueue_script( 'fvp_backend', FVP_URL . 'js/backend.js', array( 'jquery','jquery.autosize'), FVP_VERSION ); 		// development

			$upload_dir = wp_upload_dir();
			wp_localize_script( 'fvp_backend', 'fvp_backend_data', array(
				'wp_upload_dir' 	=> $upload_dir['baseurl'],
				'default_value' 	=> $this->default_value,
				'default_value_sec' => $this->default_value_sec
			) );
		}

		wp_enqueue_style( 'fvp_backend', FVP_URL . 'css/backend-min.css', array(), FVP_VERSION ); 	// production
		//wp_enqueue_style( 'fvp_backend', FVP_URL . 'css/backend.css', array(), FVP_VERSION ); 		// development
	}

	/**
	 * Registers the metabox on post/page edit views.
	 *
	 * @since 1.0
	 */
	function metabox_register() {
		$post_types = get_post_types(array("public" => true));
		foreach ($post_types as $post_type) {
			if($post_type != 'attachment')
				add_meta_box("featured_video_plus-box", __('Featured Video', 'featured-video-plus'), array( &$this, 'metabox_content' ), $post_type, 'side', 'core');
		}
	}

	/**
	 * Callback function of the metabox; generates the HTML content.
	 *
	 * @since 1.0
	 */
	function metabox_content() {
		wp_nonce_field( FVP_NAME, 'fvp_nonce');

		if( isset($_GET['post']) )
			$post_id = $_GET['post'];
		else
			$post_id = $GLOBALS['post']->ID;

		// required for conditionals
		$tmp1 = get_post_thumbnail_id($post_id);
		$tmp2 = get_post_meta( $tmp1, '_fvp_image', true);
		$has_featimg = empty($tmp1) ? false : true;
		$featimg_is_fvp = empty($tmp2) ? false : true;
		$has_post_video = $this->featured_video_plus->has_post_video($post_id);

		$options = get_option( 'fvp-settings' );
		$meta = unserialize( get_post_meta($post_id, '_fvp_video', true) );

		echo "\n\n\n<!-- Featured Video Plus Metabox -->\n";

		// WordPress Version not supported error
		if( get_bloginfo('version') < 3.1 )
			printf ('<div class="fvp_warning"><p class="description"><strong>'.__('Outdated WordPress Version', 'featured-video-plus').':</strong>&nbsp'.__('There is WordPress 3.5 out there! The plugin supports older versions way back to 3.1 - but %s is defenitly to old!', 'featured-video-plus').'</p></div>', get_bloginfo('version') );

		// displays the current featured video
		if( $has_post_video )
			echo $this->featured_video_plus->get_the_post_video( $post_id, array(256,144) );

		// input box containing the featured video URL
		$full = isset($meta['full']) ? $meta['full'] : $this->default_value;
		$legal= isset($meta['valid']) && !$meta['valid'] ? ' fvp_invalid' : '';
		echo '<textarea class="fvp_input'.$legal.'" id="fvp_video" name="fvp_video" type="text" title="' . $this->default_value . '" />' . $full . '</textarea>' . "\n";

		$sec = isset($meta['sec']) ? $meta['sec'] : $this->default_value_sec;
		echo '<textarea class="fvp_input" id="fvp_sec" name="fvp_sec" type="text" title="' . $this->default_value_sec . '" />' . $sec . '</textarea>' . "\n";

		// local video format warning
		echo '<div id="fvp_localvideo_format_warning" class="fvp_warning fvp_hidden">'."\n\t".'<p class="description">'."\n\t\t";
		echo '<span style="font-weight: bold;">'.__('Supported Video Formats', 'featured-video-plus').':</span> <code>mp4</code>, <code>webM</code> '.__('or', 'featured-video-plus').' <code>ogg/ogv</code>. <a href="http://wordpress.org/extend/plugins/featured-video-plus/faq/">'.__('More information', 'featured-video-plus').'</a>.';
		echo "\n\t</p>\n</div>\n";

		// local videos not distinct warning
		echo '<div id="fvp_localvideo_notdistinct_warning" class="fvp_warning fvp_hidden">'."\n\t".'<p class="description">'."\n\t\t";
		echo '<span style="font-weight: bold;">'.__('Fallback Video', 'featured-video-plus').':</span>&nbsp;'.__('The two input fields should contain the same video but in distinct formats.', 'featured-video-plus');
		echo "\n\t</p>\n</div>\n";

		// how to use a local videos notice
		$wrap  = get_bloginfo('version') >= 3.3 ? '-wrap' : '';
		$class = isset($meta['full']) && !empty($meta['full']) && isset($meta['valid']) && $meta['valid'] ? ' fvp_hidden' : '';
		echo "<div id=\"fvp_help_notice\" class=\"fvp_notice".$class."\">\n\t<p class=\"description\">\n\t\t";
		echo '<span style="font-weight: bold;">'.__('Hint', 'featured-video-plus').':</span>&nbsp;'.sprintf(__('Take a look into the %sContextual Help%s.', 'featured-video-plus'), '<a href="#contextual-help'.$wrap.'" id="fvp_help_toggle">', '</a>');
		echo "\n\t</p>\n</div>\n";

		// no featured image warning
		$class = $has_featimg || !$has_post_video || (isset($options['overwrite']) && !$options['overwrite']) ? ' fvp_hidden' : '';
		echo '<div id="fvp_featimg_warning" class="fvp_notice'.$class.'">'."\n\t".'<p class="description">';
		echo '<span style="font-weight: bold;">Featured Image:</span>&nbsp;'.__('For automatically displaying the Featured Video a Featured Image is required.', 'featured-video-plus');
		echo "</p>\n</div>\n";

		// set as featured image
		$class = $meta['prov'] == 'local' || !$has_post_video || ($has_featimg && $featimg_is_fvp) ? ' class="fvp_hidden"' : '';
		printf('<p id="fvp_set_featimg_box"'.$class.'>'."\n\t".'<span id="fvp_set_featimg_input">'."\n\t\t".'<input id="fvp_set_featimg" name="fvp_set_featimg" type="checkbox" value="set_featimg" />'."\n\t\t".'<label for="fvp_set_featimg">&nbsp;%s</label>'."\n\t".'</span>'."\n\t".'<a style="display: none;" id="fvp_set_featimg_link" href="#">%s</a>'."\n".'</p>'."\n", __('Set as Featured Image', 'featured-video-plus'), __('Set as Featured Image', 'featured-video-plus') );

		// current theme does not support Featured Images
		if( !current_theme_supports('post-thumbnails') && $options['overwrite'] )
			echo '<p class="fvp_warning description"><span style="font-weight: bold;">'.__('The current theme does not support Featured Images', 'featured-video-plus').':</span>&nbsp;'.sprintf(__('To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>. To hide this notice deactivate &quot;<em>Replace Featured Images</em>&quot; in the %sMedia Settings%s.', 'featured-video-plus'), '<a href="'.get_admin_url(null, '/options-media.php').'">', '</a>' )."</p>\n\n";

		echo "<!-- Featured Video Plus Metabox End-->\n\n\n";
	}

	/**
	 * Saves the changes made in the metabox: Splits URL in its parts, saves provider and id, pulls the screen capture, adds it to the gallery and as featured image.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id
	 */
	public function metabox_save($post_id, $set_featimg = false){

		if (( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 	||	// Autosave, do nothing
			( defined( 'DOING_AJAX' ) && DOING_AJAX ) 			|| 	// AJAX? Not used here
			( !current_user_can( 'edit_post', $post_id ) ) 		|| 	// Check user permissions
			( false !== wp_is_post_revision( $post_id ) ) 		||	// Return if it's a post revision
			( ( isset($_POST['fvp_nonce']) && !wp_verify_nonce( $_POST['fvp_nonce'], FVP_NAME ) ) &&
			  !is_string($set_featimg) )
		   ) return;

		$meta = unserialize( get_post_meta($post_id, '_fvp_video', true) );
		if( is_string($set_featimg) ) {
			$video = $set_featimg;
			$set_featimg = true;
		} else {
			$set_featimg = isset($_POST['fvp_set_featimg']) && !empty($_POST['fvp_set_featimg']) ? true : $set_featimg;

			if( !isset($_POST['fvp_video']) || $_POST['fvp_video'] == $this->default_value )
				$video = '';
			else
				$video = trim($_POST['fvp_video']);
		}

		$sec = isset($_POST['fvp_sec']) && !empty($_POST['fvp_sec']) && $_POST['fvp_sec'] != $this->default_value_sec ? trim($_POST['fvp_sec']) : '';

		// something changed
		if( ( empty($video) ) || 							// no video or
			( isset($meta) && ($video != $meta['full'])) ) 	// different video?
			{

			if(!empty($meta)) { // different video!

				// the current featured image is part of fvp
				$tmp = get_post_meta( get_post_thumbnail_id($post_id), '_fvp_image', true);
				if( !empty( $tmp ) ) {

					// are there other posts with the same featured image?
					$tmp2 = $this->get_post_by_custom_meta( '_thumbnail_id', $meta['img'], $post_id );
					if( !empty( $tmp2 ) ) { // there aren't, so delete it
						$img = $this->get_post_by_custom_meta('_fvp_image', $meta['prov'] . '?' . $meta['id'] );
						wp_delete_attachment( $img );
						delete_post_meta( $img, '_fvp_image', $meta['prov'] . '?' . $meta['id'] );
					}

					delete_post_meta( $post_id, '_thumbnail_id' );
				}

				delete_post_meta( $post_id, '_fvp_video' );
			}

		}

		if( empty($video) )
			return;

		if( ($video == $meta['full']) &&
			(!$set_featimg) &&
			(empty($sec) || ( isset($meta['sec']) && $meta['sec'] == $sec ) ) ) // different secondary video?
			return;

		$options = get_option( 'fvp-settings' );

/*
REGEX tested using: http://www.rubular.com/
*/

		$local = wp_upload_dir();
		preg_match('/(vimeo|youtu|dailymotion|liveleak|' . preg_quote($local['baseurl'], '/') . ')/i', $video, $video_provider);
		if( isset($video_provider[1]) )
			$video_prov = $video_provider[1];
		else $video_prov = '';

		switch ($video_prov) {

			case $local['baseurl']:
				$ext = pathinfo( $video, PATHINFO_EXTENSION );
				if( !isset($ext) || ($ext != 'mp4' && $ext != 'ogv' && $ext != 'webm' && $ext != 'ogg') ) return; // wrong extension

				$video_id 		= $this->get_post_by_url($video);
				$video_prov 	= 'local';

				if( !empty($sec) ) {
					preg_match('/(' . preg_quote($local['baseurl'], '/') . ')/i', $sec, $sec_prov);
					$ext2 = pathinfo( $sec, PATHINFO_EXTENSION );
					if ( isset($sec_prov[1]) && isset($ext2) && $sec_prov[1] == $video_provider[1] && $ext != $ext2 &&
					   ($ext2 == 'mp4' || $ext2 == 'ogv' || $ext2 == 'webm' || $ext2 == 'ogg'))
						$video_sec_id = $this->get_post_by_url($sec);
					else $sec = ''; // illegal second video, remove it
				}

				break;

			case 'youtu':
				$video_prov = 'youtube';

			case 'youtube':
				//match			provider				watch		feature							id(!)					attr(!)
				//preg_match('/youtu(?:be\.com|\.be)\/(?:watch)?(?:\?feature=[^\?&]*)*(?:[\?&]v=)?([^\?&\s]+)(?:(?:[&\?]t=)((?:\d+m)?\d+s))?/', $video, $video_data);

				$pattern = '#(?:https?\:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([\w-]{11})(?:(?:\?|&)?(?:.*)(?:t=)((?:\d+m)?\d+s))?.*#x';
				preg_match($pattern, $video, $video_data);
				if( !isset($video_data[1]) )
					break;

				$response = wp_remote_get( 'http://youtube.com/get_video_info?video_id=' . $video_data[1] );
				if( is_wp_error( $response ) )
					break;
				parse_str( $response['body'], $data );
				if( isset($data['status']) && $data['status'] == 'fail' )
					break;

				$video_id = $video_data[1];
				if( isset($video_data[2] ) )
					$video_attr = $video_data[2];

				// generate video metadata
				$video_info = array(
					'title' => $data['title'],
					'description' => $data['keywords'],
					'filename' => sanitize_file_name($data['title']),
					'timestamp' => $data['timestamp'],
					'author' => $data['author'],
					'tags' => $data['keywords'],
					'img' => ( isset($data['']) && !empty($data['iurlmaxres']) ) ? $data['iurlmaxres'] : 'http://img.youtube.com/vi/' . $video_id . '/0.jpg'
				);
				break;

			case 'vimeo': // http://developer.vimeo.com/apis/simple
				//preg_match('/vimeo.com\/([^#]+)/', $video, $video_data);

				$pattern = '#(?:https?://)?(?:\w+.)?vimeo.com/(?:video/|moogaloop\.swf\?clip_id=)?(\w+)#x';
				preg_match($pattern, $video, $video_data);
				$video_id = $video_data[1];

				// title, description, upload_date, thumbnail_large, user_name, tags
				$response = wp_remote_get( 'http://vimeo.com/api/v2/video/' . $video_id . '.php' );
				if( is_wp_error( $response ) || (isset($response['response']['code']) && $response['response']['code'] == '404') )
					break;
				$data = unserialize( $response['body'] );

				// generate video metadata
				$video_info = array(
					'title' => $data[0]['title'],
					'description' => $data[0]['description'],
					'filename' => sanitize_file_name( $data[0]['title'] ),
					'timestamp' => strtotime( $data[0]['upload_date'] ),
					'author' => $data[0]['user_name'],
					'tags' => $data[0]['tags'],
					'img' => $data[0]['thumbnail_large'],
					'url' => $data[0]['url']
				);
				break;

			case 'dailymotion': // http://www.dailymotion.com/doc/api/obj-video.html
				preg_match('/dailymotion.com\/video\/([^_#]+)/', $video, $video_data);
				if( !isset($video_data[1]) )
					break;

				// http://codex.wordpress.org/HTTP_API
				//thumbnail_url,aspect_ratio,description,created_time,embed_url,owner (owner.screenname),tags,title,url
				$url = 'https://api.dailymotion.com/video/'.$video_data[1].'?fields=title,description,created_time,owner.screenname,tags,thumbnail_url,thumbnail_large_url,url,aspect_ratio';
				$request = new WP_Http;
				$result = $request->request( $url, array( 'method' => 'GET', 'sslverify' => false) );
				if( is_wp_error($result) )
					break;
				$data = json_decode($result['body'], true);
				if( !isset($data) || (isset($data['error']['code']) && ($data['error']['code'] == 501 || $data['error']['code'] == 400) ) )
					break;

				$video_id = $video_data[1];

				// generate video metadata
				$video_info = array(
					'title' => $data['title'],
					'description' => $data['description'],
					'filename' => sanitize_file_name($data['title']),
					'timestamp' => $data['created_time'],
					'author' => $data['owner.screenname'],
					'tags' => $data['tags'],
					'img' => ( isset($data['thumbnail_url']) && !empty($data['thumbnail_url']) ) ? $data['thumbnail_url'] : $data['thumbnail_large_url'],
					'url' => $data['url']
				);
				break;

			case 'liveleak': // view-source:http://www.liveleak.com/view?i=45f_1358105976&ajax=1
				preg_match('/(?:http:\/\/)?(?:www\.)?liveleak.com\/view\?i=([\d\w]{3}_\d{10})/', $video, $video_data);
				if( !isset($video_data[1]) )
					break;

				$response = wp_remote_get( 'http://liveleak.com/view?i='.$video_data[1].'&ajax=1');
				if( is_wp_error( $response ) )
					break;

				preg_match('#jwplayer\("player_([\d\w]{12})[\d\w]{2}"\)\.setup\({([^}}\))]+)#', $response['body'], $llmeta);
				if( isset($llmeta[1]) || isset($llmeta[2]) ) {

					$video_id = $llmeta[1];

					$llmeta = explode(',', $llmeta[2]);
					foreach( $llmeta as $line ) {
						$thisline = explode(': ', $line);
						$data[trim($thisline[0])] = trim($thisline[1]);
					}

					preg_match('#class="section_title".*>([\s\w]+)</span>#', $response['body'], $title);
					preg_match('#id="body_text".*><p>(.*)<\/p><\/#', $response['body'], $desc);
					$data['title'] = isset($title[1]) ? $title[1] : '';

					$video_info = array(
						'title' 		=> $data['title'],
						'description' 	=> isset($desc[1]) ? $desc[1] : '',
						'filename' 		=> sanitize_file_name($data['title']),
						'timestamp' 	=> time(),
						'author' 		=> '', // <strong>By:</strong> <a href="http://www.liveleak.com/c/k-doe">k-doe</a>
						'tags' 			=> '', // <strong>Tags:</strong> <a href="browse?q=Drive By">Drive By</a>, <a href="browse?q=Fire Extinguisher">Fire Extinguisher</a><br />
						'img' 			=> trim($data['image'],"\""),
						'url' 			=> 'http://liveleak.com/view?i='.$video_data[1]
					);
					break;
				}
				$video_prov = 'prochan';
				$type = 'iframe';

				case 'prochan':
					if($type == 'iframe') {
						preg_match('#<iframe.*src="(?:http://)?(?:www\.)?prochan.com/embed\?f=([\d\w]{3}_\d{10})".*></iframe>#', $response['body'], $proframe);
						if( !isset($proframe[1]) )
							break;
						$video_id = $proframe[1];
					}
					break;
		}

		$valid = true;
		if( !isset($video_id) )
			$valid = false;

		// do we have a screen capture to pull?
		if( isset($video_info['img']) && !empty($video_info['img']) ) {

			// is this screen capture already existing in our media library?
			$video_img = $this->get_post_by_custom_meta('_fvp_image', $video_prov . '?' . $video_id);
			if( !isset($video_img) ) {

				// generate attachment post metadata
				$video_img_data = array(
					'post_content' => $video_info['description'],
					'post_title' => $video_info['title'],
					'post_name' => $video_info['filename']
				);

				// pull external img to local server and add to media library
				include_once( FVP_DIR . 'php/somatic_attach_external_image.php' );
				$video_img = somatic_attach_external_image($video_info['img'], $post_id, false, $video_info['filename'], $video_img_data);

				// generate picture metadata
				$video_img_meta = wp_get_attachment_metadata( $video_img );
				$video_img_meta['image_meta'] = array(
					'aperture' => 0,
					'credit' => $video_id,
					'camera' => $video_prov,
					'caption' => $video_info['description'],
					'created_timestamp' => $video_info['timestamp'],
					'copyright' => $video_info['author'],
					'focal_length' => 0,
					'iso' => 0,
					'shutter_speed' => 0,
					'title' => $video_info['title']
				);

				// save picture metadata
				wp_update_attachment_metadata($video_img, $video_img_meta);
				update_post_meta( $video_img, '_fvp_image', $video_prov . '?' . $video_id );
			}

			if( (get_bloginfo('version') >= 3.1) 	&& // set_post_thumbnail was added in 3.1
				( (!has_post_thumbnail( $post_id )) ||
				  ($set_featimg) ) )
				set_post_thumbnail( $post_id, $video_img );

		}

		$meta = array(
			'full' 	=> ( isset($data['url']) && !empty($data['url']) ) ? $data['url'] : $video,
			'id' 	=> isset($video_id) ? $video_id : '',
			'sec' 	=> isset($sec) ? $sec : '',
			'sec_id'=> ( isset($video_sec_id) && !empty($video_sec_id) ) ? $video_sec_id : '',
			'img' 	=> isset($video_img) ? $video_img : '',
			'prov' 	=> isset($video_prov) ? $video_prov : '',
			'attr' 	=> isset($video_attr) ? $video_attr : '',
			'valid' => $valid
		);

		update_post_meta( $post_id, '_fvp_video', serialize($meta) );

		return;
	}

	/**
	 * Initializes the help texts.
	 *
	 * @since 1.3
	 */
	public function help() {
		$mediahref 	= (get_bloginfo('version') >= 3.5) ? '<a href="#" class="insert-media" title="Add Media">' : '<a href="media-upload.php?post_id=4&amp;type=video&amp;TB_iframe=1&amp;width=640&amp;height=207" id="add_video" class="thickbox" title="Add Video">';
		$general 	= (get_bloginfo('version') >= 3.5) ? sprintf( __('To use local videos, copy the <code>Link To Media File</code> from your %sMedia Library%s and paste it into the text field.', 'featured-video-plus'), $mediahref, '</a>' ) :
														 sprintf( __('To use local videos, copy the <code>File URL</code> from your %sMedia Library%s and paste it into the text field.', 			 'featured-video-plus'), $mediahref, '</a>' );

		$this->help_localmedia = '
<h4 style="margin-bottom: 0;"></h4>
<p>'.$general.'&nbsp;'.__('The second text field is intended to hold the URL to the same video in a different format. It will be used as fallback if the primary file can not be played.','featured-video-plus').'&nbsp;<a href="http://videojs.com/#section4" target="_blank">'.__('More information','featured-video-plus').'</a>.</p>
<h4 style="margin-bottom: 0;">'.__('Supported Video Formats','featured-video-plus').':</h4>
<p style="margin-top: 0;"><code>webM</code>, <code>mp4</code>, <code>ogg/ogv</code></p>
<h4 style="margin-bottom: 0;">'.__('Converting your videos','featured-video-plus').':</h4>
<p style="margin-top: 0;">'.sprintf(__('Take a look at the %sMiro Video Converter%s. It is open source, lightweight and compatible with Windows, Mac and Linux.','featured-video-plus'),'<a href="http://www.mirovideoconverter.com/" target="_blank">','</a>').'</p>
<h4 style="margin-bottom: 0;">'.__('Fixing upload errors','featured-video-plus').':</h4>
<ul style="margin-top: 0;">
<li>'.sprintf(__('Read %sthis%s on how to increase the <strong>maximum file upload size</strong>.','featured-video-plus'),'<a href="http://www.wpbeginner.com/wp-tutorials/how-to-increase-the-maximum-file-upload-size-in-wordpress/" target="_blank">','</a>').'</li>
<li>'.sprintf(__('WordPress by default does not support <code>webM</code>. The plugin activates it, but under some conditions this might not be enough. %sHere%s you can get more information on this.','featured-video-plus'),'<a href="http://ottopress.com/2011/howto-html5-video-that-works-almost-everywhere/" target="_blank">','</a>').'</li>
</ul>
<h4 style="margin-bottom: 0;">'.__('Flash Fallback','featured-video-plus').':</h4>
<p style="margin-top: 0;">'.sprintf(__('The video player, %sVIDEOJS%s, features an Adobe Flash fallback. All you need to do is provide an <code>mp4</code>-video.', 'featured-video-plus'),'<a href="http://videojs.com/" target="_blank">','</a>')."</p>\n";

		$dir = wp_upload_dir();
		$this->help_urls = '
<p>'.__('These are some of the tested URL formats. Everything in bold is required, everything in brackets is optional.','featured-video-plus').'</p>
<ul>
	<li>Local Videos:
	<ul><li><code><strong>'.$dir['baseurl'].'/<em>FOLDER/FILENAME.webm|mp4|ogg|ogv</em></strong></code></li></ul></li>
	<li>YouTube:
	<ul><li><code>[http(s)://](www.)<strong>youtu.be/<em>ELEVENCHARS</em></strong>(?random=13)(?t=1m3s)</code></li>
	<li><code>[http(s)://](www.)<strong>youtube.com/watch?v=<em>ELEVENCHARS</em></strong>(?random=13)(?t=1m3s)</code></li>
	<li><code>[http(s)://](www.)<strong>youtube.com/v/<em>ELEVENCHARS</em></strong>(?random=13)(?t=1m3s)</code></li></ul></li>
	<li>Vimeo:
	<ul><li><code>(http(s)://)(www.)<strong>vimeo.com/<em>UNIQUEID</em></strong>(#stuff)</code></li></ul></li>
	<li>Dailymotion:
	<ul><li><code>(http(s)://)(www.)<strong>dailymotion.com/video/<em>UNIQUEID</em></strong>(_video_title)(#stuff)</code></li></ul></li>
	<li>Liveleak:
	<ul><li><code>(http(s)://)(www.)<strong>liveleak.com/view?i=<em>LIV_ELEAKUNQID</em></strong></code></li></ul></li>
</ul>'."\n";
	}

	/**
	 * Adds help tabs to contextual help. WordPress 3.3+
	 *
	 * @see http://codex.wordpress.org/Function_Reference/add_help_tab
	 *
	 * @since 1.3
	 */
	public function tabs() {
		$screen = get_current_screen();
		if( $screen->id != 'post' )
			return;

		if( get_bloginfo('version') >= 3.3 ) {
			// PHP FUNCTIONS HELP TAB
			$screen->add_help_tab( array(
				'id' => 'fvp_help_localvideos',
				'title'   => __('Featured Video','featured-video-plus').':&nbsp;'.__('Local Media', 'featured-video-plus'),
				'content' => $this->help_localmedia
			));

			// SHORTCODE HELP TAB
			$screen->add_help_tab( array(
				'id' => 'fvp_help_urls',
				'title'   => __('Featured Video','featured-video-plus').':&nbsp;'.__('Valid URLs', 'featured-video-plus'),
				'content' => $this->help_urls
			));
		}
	}

	/**
	 * Adds help text to contextual help. WordPress 3.3-
	 *
	 * @see http://wordpress.stackexchange.com/a/35164
	 *
	 * @since 1.3
	 */
	public function help_pre_33( $contextual_help, $screen_id, $screen ) {
		if( $screen->id != 'post' )
			return $contextual_help;

		$contextual_help .= '<hr /><h3>'.__('Featured Video','featured-video-plus').':&nbsp;'.__('Local Media', 'featured-video-plus').'</h3>';
		$contextual_help .= $this->help_localmedia;
		$contextual_help .= '<h3>'.__('Featured Video','featured-video-plus').':&nbsp;'.__('Valid URLs', 'featured-video-plus').'</h3>';
		$contextual_help .= $this->help_urls;

		return $contextual_help;
	}

	/**
	 * Function to allow more upload mime types.
	 *
	 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/upload_mimes
	 * @since 1.2
	 */
	function add_upload_mimes( $mimes=array() ) {
		$mimes['webm'] = 'video/webm';

		return $mimes;
	}

	/**
	 * Adds a media settings link to the plugin info
	 *
	 * @since 1.2
	 */
	function plugin_action_link($links, $file) {
		if ($file == FVP_NAME . '/' . FVP_NAME . '.php') {
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-media.php">Media Settings</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	/**
	 * Gets a post by an meta_key meta_value pair. Returns it's post_id.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @see http://dev.mysql.com/doc/refman/5.0/en/regexp.html#operator_regexp
	 * @since 1.0
	 *
	 * @param string $meta_key which meta_key to look for
	 * @param string $meta_value which meta_value to look for
	 */
	function get_post_by_custom_meta($meta_key, $meta_value, $notThisId = 0) {
		global $wpdb;
		if( $notThisId > 0 )
			$prepared = $wpdb->prepare(
							"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s AND post_id!=%d;",
							$meta_key, $meta_value, $notThisId
						);
		else
			$prepared = $wpdb->prepare(
							"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s;",
							$meta_key, $meta_value
						);

		return $wpdb->get_var( $prepared );
	}

	/**
	 * Gets post id by it's url / guid.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @since 1.0
	 *
	 * @param string $url which url to look for
	 */
	function get_post_by_url($url) {
		global $wpdb;
		$id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE guid=%s;",
					$url
				)
			);
		return $id;
	}
}
?>