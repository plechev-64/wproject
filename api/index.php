<?php

use Core\MainConfig;
use Core\Doctrine\LocalDateTimeType;
use Core\ORM;
use Core\Rest\RestApi;
use Core\Rest\RouteData;
use Doctrine\DBAL\Types\Type;

require_once '../vendor/autoload.php';

/** @var RestApi $restApi */
$restApi = Core\Container\Container::getInstance()->get( RestApi::class );
$restApi->setControllers( MainConfig::CONTROLLERS );
$restApi->shortInit();

/** @var RouteData|null $controller */
$routeData = $restApi->getRouteDataByRoutePath( $_GET['route'] );

if ( $routeData ) {

	if($routeData->isShortInit()){
		define( 'SHORTINIT', true );
		define( 'SHORT_AJAX', true );
	}

	require_once( '../wp-load.php' );

	if($routeData->isShortInit()) {

		define( 'DON_THEME_PATH', sprintf( '%s/themes/%s', WP_CONTENT_DIR, get_option( 'stylesheet' ) ) );
		define( 'DON_THEME_URL', sprintf( '%s/wp-content/themes/%s', get_option( 'siteurl' ), get_option( 'stylesheet' ) ) );

		Type::overrideType( 'datetime', LocalDateTimeType::class );

		( ORM::get() )->init( [
			'dbname'   => DB_NAME,
			'user'     => DB_USER,
			'password' => DB_PASSWORD,
			'host'     => DB_HOST,
			'driver'   => 'mysqli',
			'charset'  => DB_CHARSET,
		] );
	}

	if ( $routeData->getActionMethod() === 'POST' ) {
		$postBody = json_decode( file_get_contents( 'php://input' ), true );
		if ( ! $postBody ) {
			foreach($_POST as &$p){
				$p = is_string($p)? json_decode( wp_unslash($p), true): $p;
			}
			$postBody = $_POST;
		}
	} else {
		$postBody = $_GET;
	}
	$response = $restApi->routeHandle( $routeData, $postBody );
	$restApi->sendResponse( $response );
}

print_r( $_GET['route'] );
exit;
