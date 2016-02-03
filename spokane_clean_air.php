<?php

/**
 * Plugin Name: Spokane Clean Air Badge
 * Plugin URI: http://www.spokanecleanair.org
 * Description: Display the Spokane Clean Air Forecast on Your Wordpress Site
 * Version: 1.0.0
 * Author: Tony DeStefano
 * Author URI: https://www.facebook.com/TonyDeStefanoWeb
 * Text Domain:
 * Domain Path:
 * Network:
 * License: GPL2
 */

class SpokaneCleanAir {

	const VERSION = '1.0.0';
	const CSS_VERSION = '1.0.1';
	const API = 'https://spokanecleanair.org/api/v1.php';

	public function init()
	{
		add_thickbox();
		wp_enqueue_style( 'spokane-clean-air-css', plugin_dir_url( __FILE__ ) . 'spokane-clean-air.css', array(), self::CSS_VERSION );
	}

	public function short_code()
	{
		$response = wp_remote_get( self::API );

		if ( !$response || !is_array( $response ) || empty( $response ) || $response['response']['code'] != 200 )
		{
			/* Nothing to see here ... */
		}
		else
		{
			$data = json_decode( $response['body'] );

			if ( $data->success == 1 )
			{
				$html = "
					<div id='srcaa-discussion' style='display:none' title='Forecast Discussion'>
		                <p>
		                    " . ( ( strlen( $data->discussion ) > 0 ) ? $data->discussion : 'There is no current forecast discussion. Please check back later' ) . "
		                </p>
		                <p>
		                    <a href='https://www.spokanecleanair.org/current-air-quality/badge-refer' target='_blank'>Click here</a>
		                    to visit the Spokane Regional Clean Air Agency website.
		                </p>
					</div>

					<div class='srcaa-badge'>
						<a href='#TB_inline?width=600&height=300&inlineId=srcaa-discussion' class='thickbox' title='Forecast Discussion'>
							<img src='https://www.spokanecleanair.org/images/spokanecleanair/badge/badge-back.png' border='0' alt='Spokane Clean Air Agency' title='Spokane Clean Air Agency'>
						</a>
						<div class='srcaa-badge-forecast srcaa-badge-forecast-a'>
							<img src='https://www.spokanecleanair.org/badge/today' border='0'>
						</div>
						<div class='srcaa-badge-forecast srcaa-badge-forecast-b'>
							<img src='https://www.spokanecleanair.org/badge/tomorrow' border='0'>
						</div>
					</div>";

				return $html;
			}
		}

		return '<span class="srcaa-alert">The Spokane Clean Air forecast is currently unavailable. Please check back later.</span>';
	}

	function add_settings_link( $links )
	{
		$settings_link = '<a href="options-general.php?page=' . plugin_basename( __FILE__ ) . '">' . __( 'Instructions' ) . '</a>';
		$links[] = $settings_link;
		return $links;
	}

	function add_settings_page()
	{
		add_options_page(
			'Spokane Clean Air Badge Instructions',
			'Spokane Clean Air',
			'manage_options',
			plugin_basename( __FILE__ ),
			array( $this, 'options_page')
		);
	}

	function options_page()
	{
		echo "
			<div class='wrap'>
        		<h2>Spokane Clean Air Badge Instructions</h2>
        		<p>To add the badge to any Wordpress page, simply copy and paste the below shortcode into the content section of your page:</p>

    			[spokane_clean_air]

    			<p><strong>The badge will appear like this:</strong></p>

    			" . $this->short_code() . "

    		</div>";
	}
}

$sca = new SpokaneCleanAir();

/* include JS and CSS files */
add_action( 'init', array( $sca, 'init' ) );

/* register shortcode */
add_shortcode ( 'spokane_clean_air', array( $sca, 'short_code') );

if ( is_admin() )
{
	/* add the instructions page link */
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $sca, 'add_settings_link' ) );

	/* add the instructions page */
	add_action( 'admin_menu', array( $sca, 'add_settings_page' ) );
}
