<?php
/**
 * @package Habari
 * @subpackage Gravatar
 *
 * To use, add <img src="<? echo $comment->gravatar ?>">
 * 
 */

/**
 * All plugins must extend the Plugin class to be recognized.
 */
class Gravatar extends Plugin {

	public function action_init() {
		$this->add_template( 'gravatar', dirname(__FILE__) . '/gravatar.php' );
		$this->load_text_domain( 'Gravatar' );
	}
	
	/**
	 * Return a URL to the author's Gravatar based on his e-mail address.
	 *
	 * @param object $comment The Comment object to build a Gravatar URL from.
	 * @return string URL to the author's Gravatar.
	 */
	public function filter_comment_gravatar( $out, $comment ) { 
		// The Gravar ID is an hexadecimal md5 hash of the author's e-mail address.
		$query_arguments= array( 'gravatar_id' => md5( strtolower( trim( $comment->email ) ) ) );
		// Retrieve the Gravatar options.
		$options= Options::get( array( 'gravatar__default', 'gravatar__size', 'gravatar__rating' ) );
		foreach ( $options as $key => $value ) {
			if ( $value != '' ) {
				// We only want "default, size, rating".
				list( $junk, $key )= explode( '__', $key );
				$query_arguments[$key]= $value;
			}
		}
		// Ampersands need to be encoded to &amp; for HTML to validate.
		$query= http_build_query( $query_arguments, '', '&amp;' );
		$url= "http://www.gravatar.com/avatar.php?" . $query;
		
		$url = Plugins::filter('gravatar_url', $url, $comment);
		
		return $url;
	}
	
	public function theme_comment_gravatar( $theme, $comment ) {
		$theme->gravatar_comment = $comment;
		return $theme->fetch( 'gravatar' );
	}
	
	/**
	 * Handle calls from FormUI actions.
	 * Show the form to manage the plugin's options.
	 *
	 */
	public function configure() {
        $ui= new FormUI( 'gravatar' );
		$g_s_d= $ui->append( 'text', 'default', 'gravatar__default', '<dl><dt>' . _t('Default Gravatar', __CLASS__) . '</dt><dd>' . _t('An optional "default" parameter may follow that specifies the full, URL encoded URl, protocol included of a GIF, JPEG or PNG image that should be returned if either the request email address has no associated gravatar, or that gravatar has a rating higher than is allowed by the "rating" parameter.', __CLASS__) . '</dd></dl>' );
		$g_s_s= $ui->append( 'text', 'size', 'gravatar__size', '<dl><dt>' . _t('Size', __CLASS__). '</dt><dd>' . _t('An optional "size" parameter may follow that specifies the desired width and height of the gravatar. Valid vaues are from 1 to 80 inclusive. Any size other than 80 will cause the original gravatar image to be downsampled using bicubic resampling before output.', __CLASS__) . '</dd></dl>' );
		//mark size as required
		$g_s_s->add_validator( 'validate_required' );

		$g_s_r= $ui->append( 'select', 'rating', 'gravatar__rating', '<dl><dt>' . _t('Rating', __CLASS__) . '</dt><dd>' . _t('An optional "rating" parameter may follow with a value of [ G | PG | R | X ] that determines the highest rating (inclusive) that will be returned.', __CLASS__) . '</dd></dl>', array( 'G' => 'G', 'PG' => 'PG', 'R' => 'R', 'X' => 'X' ) );
		$ui->append( 'submit', 'save', _t('Save') );
		$ui->out();
	}
	
}
?>
