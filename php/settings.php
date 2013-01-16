<?php
/**
 * Class containing everything regarding plugin settings on media-settings.php
 *
 * @author ahoereth
 * @version 2013/01/09
 * @see ../featured_video_plus.php
 * @since 1.3
 */
class featured_video_plus_settings {
	private $help_shortcode;
	private $help_functions;

	/**
	 * Initialises the plugin settings section, the settings fields and registers the options field and save function.
	 *
	 * @see http://codex.wordpress.org/Settings_API
	 * @since 1.0
	 */
	function settings_init() {
		add_settings_section('fvp-settings-section', 		'Featured Videos', 										array( &$this, 'settings_content' ), 	'media');

		add_settings_field('fvp-settings-overwrite', 	__('Replace Featured Images', 'featured-video-plus'), 		array( &$this, 'settings_overwrite' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-sizing', 		__('Video Sizing', 'featured-video-plus'), 					array( &$this, 'settings_sizing' ), 	'media', 'fvp-settings-section');
		//add_settings_field('fvp-settings-videojs', 		__('VIDEOJS Player Options', 'featured-video-plus'), 		array( &$this, 'settings_videojs' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-youtube', 		__('YouTube Player Options', 'featured-video-plus'), 		array( &$this, 'settings_youtube' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-vimeo', 		__('Vimeo Player Options', 'featured-video-plus'), 			array( &$this, 'settings_vimeo' ), 		'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-dailymotion', 	__('Dailymotion Player Options', 'featured-video-plus'), 	array( &$this, 'settings_dailymotion' ),'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-rate', 		__('Support', 'featured-video-plus'), 						array( &$this, 'settings_rate' ), 		'media', 'fvp-settings-section');

		register_setting('media', 'fvp-settings', array( &$this, 'settings_save' ));
	}

	/**
	 * The settings section content. Describes the plugin settings, the php functions and the WordPress shortcode.
	 *
	 * @since 1.0
	 */
	function settings_content() {
		$wrap = get_bloginfo('version') >= 3.3 ? '-wrap' : ''; ?>

<p>
<?php printf(__('To display your featured videos you can either make use of the automatic replacement, use the %s or manually edit your theme\'s source files to make use of the plugins PHP-functions.', 'featured-video-plus'), '<code>[featured-video-plus]</code>-Shortcode'); ?>
<?php printf(__('For more information about Shortcode and PHP functions see the %sContextual Help%s.', 'featured-video-plus'), '<a href="#contextual-help'.$wrap.'" id="fvp_help_toggle">', '</a>'); ?>
</p>

<?php }

	/**
	 * Displays the setting if the plugin should display the featured video in place of featured images.
	 *
	 * @since 1.0
	 */
	function settings_overwrite() {
		$options = get_option( 'fvp-settings' );
		$overwrite = isset($options['overwrite']) ? $options['overwrite'] : false;
?>

<input type="radio" name="fvp-settings[overwrite]" id="fvp-settings-overwrite-1" value="true" 	<?php checked( true, 	$overwrite, true ) ?>/><label for="fvp-settings-overwrite-1">&nbsp;<?php _e('yes', 'featured-video-plus'); ?>&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[overwrite]" id="fvp-settings-overwrite-2" value="false" 	<?php checked( false, 	$overwrite, true ) ?>/><label for="fvp-settings-overwrite-2">&nbsp;<?php _e('no', 'featured-video-plus'); ?></label>
<p class="description"><?php _e('If a Featured Video is available it can be displayed in place of the Featured Image. Still, a Featured Image is required.', 'featured-video-plus'); ?></p>

<?php
$class = $overwrite ? 'fvp_warning ' : 'fvp_notice ';
if( !current_theme_supports('post-thumbnails') )
	echo '<p class="'.$class.'description"><span style="font-weight: bold;">'.__('The current theme does not support Featured Images', 'featured-video-plus').':</span>&nbsp;'.__('To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>.', 'featured-video-plus').'</p>'."\n";

}

	/**
	 * Displays the setting if the plugin should fit the width of the videos automatically or use fixed widths.
	 *
	 * @since 1.3
	 */
	function settings_sizing() {
		$options = get_option( 'fvp-settings' );
		$wmode = isset($options['sizing']['wmode']) && $options['sizing']['wmode'] == 'auto' ? 'auto' : 'fixed';
		$hmode = isset($options['sizing']['hmode']) && $options['sizing']['hmode'] == 'auto' ? 'auto' : 'fixed';
		$width = isset($options['sizing']['width' ]) ? $options['sizing']['width' ] : 560;
		$height= isset($options['sizing']['height']) ? $options['sizing']['height'] : 315;
		$wclass= $wmode == 'auto' ? ' fvp_readonly' : '';
		$hclass= $hmode == 'auto' ? ' fvp_readonly' : '';
		$align = isset($options['sizing']['align']) ? $options['sizing']['align'] : 'left'; ?>

<span class="fvp_toggle_input">
	<label class="fvp_grouplable"><?php _e('Width', 'featured-video-plus'); ?>:</label>
	<span class="fvp_grouppart1">
		<input class="fvp_toggle" type="checkbox" name="fvp-settings[sizing][width][auto]" id="fvp-settings-width-auto" value="auto" <?php checked( 'auto', $wmode, true ) ?>/>
		<label for="fvp-settings-width-auto">&nbsp;auto&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>
	</span>
	<input class="fvp_input<?php echo $wclass; ?>" type="text" name="fvp-settings[sizing][width][fixed]" id="fvp-settings-width-fixed" value="<?php echo $width; ?>" size="4" maxlength="4" style="text-align: right; width: 3em;" <?php if('auto'==$wmode) echo 'readonly="readonly"'; ?>/>
	<label for="fvp-settings-width-fixed">&nbsp;px</label>
</span>
<br />
<span class="fvp_toggle_input">
	<label class="fvp_grouplable"><?php _e('Height', 'featured-video-plus'); ?>:</label>
	<span class="fvp_grouppart1">
		<input class="fvp_toggle" type="checkbox" name="fvp-settings[sizing][height][auto]" id="fvp-settings-height-auto" value="auto" <?php checked( 'auto', $hmode, true ) ?>/>
		<label for="fvp-settings-height-auto">&nbsp;auto&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>
	</span>
	<input class="fvp_input<?php echo $hclass; ?>" type="text" name="fvp-settings[sizing][height][fixed]" id="fvp-settings-height-fixed" value="<?php echo $height; ?>" size="4" maxlength="4" style="text-align: right; width: 3em;" <?php if('auto'==$hmode) echo 'readonly="readonly"'; ?>/>
	<label for="fvp-settings-height-fixed">&nbsp;px</label>
</span>
<p class="description">
	<?php _e('When using <code>auto</code> the video will be adjusted to fit it\'s parent element while sticking to it\'s ratio. Using a <code>fixed</code> height and width might result in <em>not so pretty</em> black bars.', 'featured-video-plus'); ?>
</p>
<span class="fvp_toggle_input">
	<label class="fvp_grouplable"><?php _e('Align', 'featured-video-plus'); ?>:</label>
	<span class="fvp_grouppart1">
		<input type="radio" name="fvp-settings[sizing][align]" id="fvp-settings-align-1" value="left" 	<?php checked( 'left', 	$align, true ) ?>/><label for="fvp-settings-align-1">&nbsp;<?php _e('left', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="fvp-settings[sizing][align]" id="fvp-settings-align-2" value="center" <?php checked( 'center',$align, true ) ?>/><label for="fvp-settings-align-2">&nbsp;<?php _e('center', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="fvp-settings[sizing][align]" id="fvp-settings-align-2" value="right"	<?php checked( 'right', $align, true ) ?>/><label for="fvp-settings-align-3">&nbsp;<?php _e('right', 'featured-video-plus'); ?></label>
	</span>
</span>

<?php }

	/**
	 * Displays the settings to style the VIDEOJS player.
	 *
	 * @see https://github.com/zencoder/video-js/blob/master/docs/skins.md
	 * @see http://jlofstedt.com/moonify/
	 * @see http://videojs.com/
	 * @since 1.3
	 */
	function settings_videojs() {
		$options = get_option( 'fvp-settings' );
		$videojs['skin'] 	= isset($options['videojs']['skin']) ? $options['videojs']['skin'] : 'default'; ?>

<input type="radio" name="fvp-settings[videojs][skin]" id="fvp-settings-videojs-skin-1" value="videojs" <?php checked( 'videojs', $videojs['skin'], true ) ?>/><label for="fvp-settings-videojs-skin-1">&nbsp;<?php _e('default', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[videojs][skin]" id="fvp-settings-videojs-skin-2" value="tubecss" <?php checked( 'tubecss', $videojs['skin'], true ) ?>/><label for="fvp-settings-videojs-skin-2">&nbsp;TubeCSS</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[videojs][skin]" id="fvp-settings-videojs-skin-3" value="moonify" <?php checked( 'moonify', $videojs['skin'], true ) ?>/><label for="fvp-settings-videojs-skin-3">&nbsp;Moonify</label>&nbsp;(<a style="font-style: italic;" href="http://jlofstedt.com/moonify/" target="_blank">info</a>)&nbsp;&nbsp;&nbsp;&nbsp;

<?php
		__('VIDEOJS Player Options', 'featured-video-plus'); // translate it, even if not yet integrated
	}

	/**
	 * Displays the settings to style the YouTube video player.
	 *
	 * @see https://developers.google.com/youtube/player_parameters
	 * @since 1.3
	 */
	function settings_youtube() {
		$options = get_option( 'fvp-settings' );
		$youtube['theme'] 	= isset($options['youtube']['theme']) 	? $options['youtube']['theme'] 	: 'dark';
		$youtube['color'] 	= isset($options['youtube']['color']) 	? $options['youtube']['color'] 	: 'red';
		$youtube['info'] 	= isset($options['youtube']['info']) 	? $options['youtube']['info'] 	: 1;
		$youtube['rel'] 	= isset($options['youtube']['rel']) 	? $options['youtube']['rel'] 	: 1;
		$youtube['fs'] 		= isset($options['youtube']['fs']) 		? $options['youtube']['fs'] 	: 1; ?>

<input type="checkbox" name="fvp-settings[youtube][theme]" 	id="fvp-settings-youtube-theme" value="light" 	<?php checked( 'light', $youtube['theme'], 	1 ) ?>/><label for="fvp-settings-youtube-theme">&nbsp;<?php _e('Light Theme', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][color]" 	id="fvp-settings-youtube-color" value="white" 	<?php checked( 'white', $youtube['color'], 	1 ) ?>/><label for="fvp-settings-youtube-color">&nbsp;<?php _e('White Progressbar', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][info]" 	id="fvp-settings-youtube-info" 	value="true" 	<?php checked( 1, 		$youtube['info'], 	1 ) ?>/><label for="fvp-settings-youtube-info">&nbsp;<?php _e('Show Info', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][fs]" 	id="fvp-settings-youtube-fs" 	value="true" 	<?php checked( 1, 		$youtube['fs'], 	1 ) ?>/><label for="fvp-settings-youtube-fs">&nbsp;<?php _e('Fullscreen Button', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][rel]" 	id="fvp-settings-youtube-rel" 	value="true" 	<?php checked( 1, 		$youtube['rel'], 	1 ) ?>/><label for="fvp-settings-youtube-rel">&nbsp;<?php _e('Related Videos', 'featured-video-plus'); ?></label>

<?php
	}

	/**
	 * Displays the settings to style the vimeo video player. Default: &amp;title=1&amp;portrait=0&amp;byline=1&amp;color=00adef
	 *
	 * @see http://developer.vimeo.com/player/embedding
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @since 1.0
	 */
	function settings_vimeo() {
		$options = get_option( 'fvp-settings' );
		$vimeo['portrait'] 	= isset($options['vimeo']['portrait']) 	? $options['vimeo']['portrait'] : 0;
		$vimeo['title' ] 	= isset($options['vimeo']['title' ]) 	? $options['vimeo']['title' ] 	: 1;
		$vimeo['byline'] 	= isset($options['vimeo']['byline']) 	? $options['vimeo']['byline'] 	: 1;
		$vimeo['color' ] 	= isset($options['vimeo']['color' ]) 	? $options['vimeo']['color' ] 	: '00adef'; ?>

<div style="position: relative; bottom: .6em;">
	<input type="checkbox" name="fvp-settings[vimeo][portrait]" id="fvp-settings-vimeo-1" value="display" <?php checked( 1, $vimeo['portrait'], 1 ) ?>/><label for="fvp-settings-vimeo-1">&nbsp;<?php _e('Portrait', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][title]" 	id="fvp-settings-vimeo-2" value="display" <?php checked( 1, $vimeo['title'], 	1 ) ?>/><label for="fvp-settings-vimeo-2">&nbsp;<?php _e('Title', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][byline]" 	id="fvp-settings-vimeo-3" value="display" <?php checked( 1, $vimeo['byline'], 	1 ) ?>/><label for="fvp-settings-vimeo-3">&nbsp;<?php _e('Byline', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[vimeo][color]" id="fvp-settings-vimeo-color" value="#<?php echo $vimeo['color'] ?>" data-default-color="#00adef" />
		<label for="fvp-settings-vimeo-color" style="display: none;">&nbsp;<?php _e('Color', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-vimeo-colorpicker"></div><?php } ?>
	</span>
</div>
<p class="description"><?php _e('Vimeo Plus Videos might ignore these settings.', 'featured-video-plus'); ?></p>

<?php
	}

	/**
	 * Displays the settings to style the Dailymotion video player.
	 *
	 * @see https://developers.google.com/youtube/player_parameters
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @since 1.3
	 */
	function settings_dailymotion() {
		$options = get_option( 'fvp-settings' );
		$dailymotion['logo'] 		= isset($options['dailymotion']['logo']) 		? $options['dailymotion']['logo'] 		: 1;
		$dailymotion['info'] 		= isset($options['dailymotion']['info']) 		? $options['dailymotion']['info'] 		: 1;
		$dailymotion['foreground'] 	= isset($options['dailymotion']['foreground']) 	? $options['dailymotion']['foreground'] : 'f7fffd';
		$dailymotion['highlight' ] 	= isset($options['dailymotion']['highlight' ]) 	? $options['dailymotion']['highlight' ] : 'ffc300';
		$dailymotion['background'] 	= isset($options['dailymotion']['background']) 	? $options['dailymotion']['background'] : '171d1b'; ?>

	<input type="checkbox" name="fvp-settings[dailymotion][logo]" id="fvp-settings-dailymotion-logo" value="display" <?php checked( 1, $dailymotion['logo'], 1 ) ?>/><label for="fvp-settings-dailymotion-logo">&nbsp;<?php _e('Logo', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[dailymotion][info]" id="fvp-settings-dailymotion-info" value="display" <?php checked( 1, $dailymotion['info'], 1 ) ?>/><label for="fvp-settings-dailymotion-info">&nbsp;<?php _e('Videoinfo', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<br />
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[dailymotion][foreground]" id="fvp-settings-dailymotion-foreground" value="#<?php echo $dailymotion['foreground'] ?>" data-default-color="#f7fffd" />
		<label for="fvp-settings-dailymotion-foreground" style="display: none;">&nbsp;<?php _e('Foreground', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-dailymotion-foreground-colorpicker"></div><?php } ?>
	</span>
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[dailymotion][highlight]" id="fvp-settings-dailymotion-highlight" value="#<?php echo $dailymotion['highlight'] ?>" data-default-color="#ffc300" />
		<label for="fvp-settings-dailymotion-highlight" style="display: none;">&nbsp;<?php _e('Highlight', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-dailymotion-highlight-colorpicker"></div><?php } ?>
	</span>
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[dailymotion][background]" id="fvp-settings-dailymotion-background" value="#<?php echo $dailymotion['background'] ?>" data-default-color="#171d1b" />
		<label for="fvp-settings-dailymotion-background" style="display: none;">&nbsp;<?php _e('Background', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-dailymotion-background-colorpicker"></div><?php } ?>
	</span>
<?php
	}

	/**
	 * Displays info about rating the plugin, giving feedback and requesting new features
	 *
	 * @since 1.0
	 */
	function settings_rate() { ?>

<p>
	<?php _e('Found a bug or <strong>missing a specific video service</strong>?', 'featured-video-plus'); ?>
	<a href="http://wordpress.org/extend/plugins/featured-video/" title="Featured Video Plus Support Forum on wordpress.org" style="font-weight: bold;">Leave a note</a> in the support forum!<br />
	<?php _e('Do you like the plugin?', 'featured-video-plus'); ?>&nbsp;<a href="http://wordpress.org/extend/plugins/featured-video-plus/" title="Featured Video Plus on wordpress.org" style="font-weight: bold;"><?php _e('rate it', 'featured-video-plus'); ?></a>.
</p>

<?php }

	/**
	 * Function through which all settings are passed before they are saved. Validate the data.
	 *
	 * @since 1.0
	 */
	function settings_save($input) {
		$hexcolor = '/#?([0123456789abcdef]{3}[0123456789abcdef]{0,3})/i';
		$numbers = '#[0-9]{1,4}#';
		$options  = get_option( 'fvp-settings' );

		// General
		$options['overwrite'] 	= isset($input['overwrite']) && $input['overwrite'] == 'true' ? true : false;

		// Sizing
		preg_match($numbers, $input['sizing']['width' ]['fixed'], $width );
		preg_match($numbers, $input['sizing']['height']['fixed'], $height);
		$options['sizing']['width' ] = isset($width[ 0]) ? $width[ 0] : 560;
		$options['sizing']['height'] = isset($height[0]) ? $height[0] : 315;
		$options['sizing']['wmode' ] = isset($input['sizing']['width' ]['auto'])?  'auto' 			: 'fixed';
		$options['sizing']['hmode' ] = isset($input['sizing']['height' ]['auto'])? 'auto' 			: 'fixed';
		$options['sizing']['align' ] = isset($input['sizing']['align']) ? $input['sizing']['align'] : 'left';

		// VIDEOJS
		//$options['videojs']['skin'] = isset( $input['videojs']['skin'] ) ? $input['videojs']['skin'] : 'videojs';

		// YouTube
		$options['youtube']['theme'] 	= isset($input['youtube']['theme']) && ( $input['youtube']['theme']  == 'light' ) ? 'light' : 'dark';
		$options['youtube']['color'] 	= isset($input['youtube']['color']) && ( $input['youtube']['color']  == 'white' ) ? 'white' : 'red';
		$options['youtube']['info'] 	= isset($input['youtube']['info'])	&& ( $input['youtube']['info'] 	 == 'true' 	) ? 1 		: 0;
		$options['youtube']['rel'] 		= isset($input['youtube']['rel'])	&& ( $input['youtube']['rel'] 	 == 'true' 	) ? 1 		: 0;
		$options['youtube']['fs'] 		= isset($input['youtube']['fs'])	&& ( $input['youtube']['fs'] 	 == 'true' 	) ? 1 		: 0;

		// Vimeo
		$options['vimeo']['portrait'] 	= isset($input['vimeo']['portrait'])&& ( $input['vimeo']['portrait'] == 'display' ) ? 1 : 0;
		$options['vimeo']['title'] 		= isset($input['vimeo']['title']) 	&& ( $input['vimeo']['title'] 	 == 'display' ) ? 1 : 0;
		$options['vimeo']['byline'] 	= isset($input['vimeo']['byline']) 	&& ( $input['vimeo']['byline'] 	 == 'display' ) ? 1 : 0;
		if( isset($options['vimeo']['color']) ) preg_match($hexcolor, $input['vimeo']['color'], $vimeocolor);
		$options['vimeo']['color'] = isset($vimeocolor[1]) && !empty($vimeocolor[1]) ? $vimeocolor[1] : '00adef';

		// Dailymotion
		$options['dailymotion']['logo'] = isset($input['dailymotion']['logo']) && ( $input['dailymotion']['logo'] == 'display' ) ? 1 : 0;
		$options['dailymotion']['info'] = isset($input['dailymotion']['info']) && ( $input['dailymotion']['info'] == 'display' ) ? 1 : 0;
		if( isset($options['dailymotion']['foreground']) ) preg_match($hexcolor, $input['dailymotion']['foreground'], $dm_foreground);
		$options['dailymotion']['foreground'] 	= isset($dm_foreground[1]) && !empty($dm_foreground[1])? $dm_foreground[1] : 'f7fffd';
		if( isset($options['dailymotion']['highlight'])  ) preg_match($hexcolor, $input['dailymotion']['highlight'],  $dm_highlight);
		$options['dailymotion']['highlight'] 	= isset($dm_highlight[1])  && !empty($dm_highlight[1]) ? $dm_highlight[1] 	: 'ffc300';
		if( isset($options['dailymotion']['background']) ) preg_match($hexcolor, $input['dailymotion']['background'], $dm_background);
		$options['dailymotion']['background'] 	= isset($dm_background[1]) && !empty($dm_background[1])? $dm_background[1] : '171d1b';

		return $options;
	}

	/**
	 * Initializes the help texts.
	 *
	 * @since 1.3
	 */
	public function help() {
		$this->help_shortcode = '
<ul>
	<li>
		<code>[featured-video]</code><br />
		<span style="padding-left: 5px;">'.__('Displays the video in its default size.', 'featured-video-plus').'</span>
	</li>
	<li>
		<code>[featured-video width=560]</code><br />
		<span style="padding-left: 5px;">'.__('Displays the video with an width of 300 pixel. Height will be fitted to the aspect ratio.', 'featured-video-plus').'</span>
	</li>
	<li>
		<code>[featured-video width=560 height=315]</code><br />
		<span style="padding-left: 5px;">'.__('Displays the video with an fixed width and height.', 'featured-video-plus').'</span>
	</li>
</ul>'."\n";

		$this->help_functions ='
<ul>
	<li><code>the_post_video(array(width, height))</code></li>
	<li><code>has_post_video(post_id = null)</code></li>
	<li><code>get_the_post_video(post_id = null, array(width, height))</code></li>
</ul>
<p>
	'.sprintf(__('All parameters are optional. If %s the current post\'s id will be used.', 'featured-video-plus'), '<code>post_id == null</code>').'<br />
	'.sprintf(__('The functions are implemented corresponding to the original %sFeatured Image functions%s: They are intended to be used and to act the same way.', 'featured-video-plus'), '<a href="http://codex.wordpress.org/Post_Thumbnails#Function_Reference" title="Post Thumbnails Function Reference">', '</a>').'
</p>'."\n";
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
		if( ($screen->id != 'options-media') || (get_bloginfo('version') < 3.3) )
			return;

		// PHP FUNCTIONS HELP TAB
		$screen->add_help_tab( array(
			'id' => 'fvp_help_functions',
			'title'   => 'Featured Video Plus: '.__('PHP-Functions','featured-video-plus'),
			'content' => $this->help_functions
		));

		// SHORTCODE HELP TAB
		$screen->add_help_tab( array(
			'id' => 'fvp_help_shortcode',
			'title'   => 'Featured Video Plus: Shortcode',
			'content' => $this->help_shortcode
		));
	}

	/**
	 * Adds help text to contextual help. WordPress 3.3-
	 *
	 * @see http://wordpress.stackexchange.com/a/35164
	 *
	 * @since 1.3
	 */
	public function help_pre_33( $contextual_help, $screen_id, $screen ) {
		if( $screen->id != 'options-media' )
			return $contextual_help;

		$contextual_help .= '<hr /><h3>Featured Video Plus: '.__('PHP-Functions','featured-video-plus').'</h3>';
		$contextual_help .= $this->help_functions;
		$contextual_help .= '<h3>Featured Video Plus: Shortcode</h3>';
		$contextual_help .= $this->help_shortcode;

		return $contextual_help;
	}

}
?>