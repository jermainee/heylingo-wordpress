<?php
/**
 * Plugin Name:       Heylingo
 * Plugin URI:        https://heylingo.io
 * Description:       Makes your site multilingual: AI translations with translation memory, a language switcher and corrections right on your own pages.
 * Version:           0.1.0
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Heylingo
 * Author URI:        https://heylingo.io
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       heylingo
 *
 * @package Heylingo
 */

// The plugin only loads the Heylingo embed script, the same one-line snippet as on heylingo.io.
// It stores nothing: the page's Origin selects the site on api.heylingo.io, and every setting
// lives in the dashboard. The settings page asks the embed API whether this site's domain has
// an active subscription and links to the dashboard.

defined( 'ABSPATH' ) || exit;

const HEYLINGO_EMBED_URL     = 'https://cdn.heylingo.io/heylingo.min.js';
const HEYLINGO_API_URL       = 'https://api.heylingo.io';
const HEYLINGO_DASHBOARD_URL = 'https://dashboard.heylingo.io';
const HEYLINGO_PRICING_URL   = 'https://heylingo.io/#pricing';

/**
 * Enqueues the embed script on the front end.
 *
 * In `<head>` and without async or defer: the script has to hide the page before it renders
 * while the first translations load. The URL carries no version (every site runs the current
 * build), so WordPress must not append `?ver=`.
 */
function heylingo_enqueue_embed() {
	if ( is_customize_preview() ) {
		return;
	}
	/**
	 * Filters whether the Heylingo script loads on the current page.
	 *
	 * @param bool $load True to load it.
	 */
	if ( ! apply_filters( 'heylingo_load_embed', true ) ) {
		return;
	}
	wp_enqueue_script( 'heylingo', HEYLINGO_EMBED_URL, array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion, WordPress.WP.EnqueuedResourceParameters.NotInFooter
}
add_action( 'wp_enqueue_scripts', 'heylingo_enqueue_embed', 1 );

/**
 * Keeps optimization plugins from deferring, delaying or bundling the script
 * (Cloudflare Rocket Loader, LiteSpeed Cache).
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string
 */
function heylingo_script_tag( $tag, $handle ) {
	if ( 'heylingo' !== $handle ) {
		return $tag;
	}
	return str_replace( ' src=', ' data-cfasync="false" data-no-optimize="1" data-no-defer="1" src=', $tag );
}
add_filter( 'script_loader_tag', 'heylingo_script_tag', 10, 2 );

/**
 * Excludes the script from WP Rocket's "load JavaScript deferred" and "delay JavaScript execution".
 *
 * @param mixed $patterns URL patterns WP Rocket leaves alone.
 * @return array
 */
function heylingo_rocket_exclusions( $patterns ) {
	$patterns   = (array) $patterns;
	$patterns[] = 'cdn.heylingo.io';
	return $patterns;
}
add_filter( 'rocket_exclude_defer_js', 'heylingo_rocket_exclusions' );
add_filter( 'rocket_delay_js_exclusions', 'heylingo_rocket_exclusions' );

/**
 * Keeps the admin bar out of translation. It is the logged-in user's own interface, not page
 * content, and its text would only use up the visitor budget. It renders before
 * DOMContentLoaded, when the script collects the page's text.
 */
function heylingo_skip_admin_bar() {
	if ( is_admin() || ! wp_script_is( 'heylingo', 'done' ) ) {
		return;
	}
	wp_print_inline_script_tag( 'var b=document.getElementById("wpadminbar");if(b)b.setAttribute("translate","no");' );
}
add_action( 'wp_after_admin_bar_render', 'heylingo_skip_admin_bar' );

/**
 * The Origin the browser sends from this site's pages, e.g. `https://example.com`.
 *
 * @return string
 */
function heylingo_site_origin() {
	$parts = wp_parse_url( home_url() );
	$port  = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
	return $parts['scheme'] . '://' . $parts['host'] . $port;
}

/**
 * Asks the embed API what it serves for this site's Origin, as heylingo.min.js would.
 *
 * @return array{state: string, host: string, config?: array, message?: string}
 *               state is "active", "inactive" (no subscription for the domain), "setup" or "error".
 */
function heylingo_site_status() {
	$host     = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$response = wp_remote_get(
		HEYLINGO_API_URL . '/v1/embed/config',
		array(
			'timeout' => 5,
			'headers' => array( 'Origin' => heylingo_site_origin() ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array(
			'state'   => 'error',
			'host'    => $host,
			'message' => $response->get_error_message(),
		);
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 === $code && is_array( $body ) && isset( $body['languages'] ) ) {
		return array(
			'state'  => 'active',
			'host'   => $host,
			'config' => $body,
		);
	}
	if ( 403 === $code ) {
		return array(
			'state' => 'inactive',
			'host'  => $host,
		);
	}
	if ( 503 === $code ) {
		return array(
			'state' => 'setup',
			'host'  => $host,
		);
	}
	return array(
		'state'   => 'error',
		'host'    => $host,
		'message' => is_array( $body ) && isset( $body['error'] ) ? (string) $body['error'] : 'HTTP ' . $code,
	);
}

/**
 * Adds Settings → Heylingo.
 */
function heylingo_add_settings_page() {
	add_options_page( 'Heylingo', 'Heylingo', 'manage_options', 'heylingo', 'heylingo_render_settings_page' );
}
add_action( 'admin_menu', 'heylingo_add_settings_page' );

/**
 * Adds "Settings" and "Dashboard" links to the plugin's row on the Plugins screen.
 *
 * @param array $links The row's action links.
 * @return array
 */
function heylingo_action_links( $links ) {
	array_unshift(
		$links,
		sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=heylingo' ) ), esc_html__( 'Settings', 'heylingo' ) ),
		sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( HEYLINGO_DASHBOARD_URL ), esc_html__( 'Dashboard', 'heylingo' ) )
	);
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'heylingo_action_links' );

/**
 * Renders Settings → Heylingo: connection status, dashboard link, how to exclude content.
 */
function heylingo_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$status = heylingo_site_status();
	$host   = '<strong>' . esc_html( $status['host'] ) . '</strong>';
	?>
	<div class="wrap">
		<h1>Heylingo</h1>

		<?php if ( 'active' === $status['state'] ) : ?>
			<?php
			$config  = $status['config'];
			$source  = isset( $config['source']['name'] ) ? (string) $config['source']['name'] : '';
			$targets = array();
			foreach ( array_slice( (array) $config['languages'], 1 ) as $language ) {
				$targets[] = isset( $language['name'] ) ? (string) $language['name'] : '';
			}
			?>
			<div class="notice notice-success inline"><p>
				<?php
				printf(
					/* translators: 1: domain, 2: source language, 3: comma-separated target languages */
					esc_html__( 'Heylingo translates %1$s from %2$s into %3$s.', 'heylingo' ),
					$host, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
					esc_html( $source ),
					esc_html( $targets ? implode( ', ', $targets ) : __( 'no other language yet', 'heylingo' ) )
				);
				?>
			</p></div>
			<p><?php esc_html_e( 'Choose languages, the switcher position and your glossary in the dashboard. To correct a translation, open edit mode from the dashboard and change the text right on your page.', 'heylingo' ); ?></p>
			<p><a class="button button-primary" href="<?php echo esc_url( HEYLINGO_DASHBOARD_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open dashboard', 'heylingo' ); ?></a></p>

		<?php elseif ( 'inactive' === $status['state'] ) : ?>
			<div class="notice notice-warning inline"><p>
				<?php
				printf(
					/* translators: %s: domain */
					esc_html__( 'There is no active Heylingo subscription for %s yet. Until there is, the script leaves your pages as they are.', 'heylingo' ),
					$host // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				);
				?>
			</p></div>
			<p>
				<?php
				printf(
					/* translators: %s: domain */
					esc_html__( 'Subscribe on heylingo.io and enter %s as your domain at checkout (with or without "www."). Translation starts on the next page view.', 'heylingo' ),
					$host // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				);
				?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( HEYLINGO_PRICING_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'See pricing and subscribe', 'heylingo' ); ?></a>
				<a class="button" href="<?php echo esc_url( HEYLINGO_DASHBOARD_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open dashboard', 'heylingo' ); ?></a>
			</p>

		<?php elseif ( 'setup' === $status['state'] ) : ?>
			<div class="notice notice-info inline"><p>
				<?php
				printf(
					/* translators: %s: domain */
					esc_html__( 'Heylingo is setting up %s. Reload this page in a minute.', 'heylingo' ),
					$host // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				);
				?>
			</p></div>

		<?php else : ?>
			<div class="notice notice-error inline"><p>
				<?php
				printf(
					/* translators: %s: error message */
					esc_html__( 'Could not reach Heylingo: %s', 'heylingo' ),
					esc_html( $status['message'] )
				);
				?>
			</p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Keep content untranslated', 'heylingo' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: 1: CSS class name, 2: HTML attribute */
				esc_html__( 'Add the CSS class %1$s to a block (Block settings → Advanced → Additional CSS class) or the attribute %2$s to any element. Heylingo leaves it and everything inside as it is.', 'heylingo' ),
				'<code>notranslate</code>',
				'<code>translate="no"</code>'
			);
			?>
		</p>
	</div>
	<?php
}
