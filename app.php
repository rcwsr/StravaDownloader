<?php

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\GuzzleServiceProvider;
use StravaDL\Provider\StravaDownloaderServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->register(new GuzzleServiceProvider(), array(
    'guzzle.services' => '/path/to/services.json',
));
$app->register(new StravaDownloaderServiceProvider());

//Routes

$app->get('/', function() use ($app){

    try{
        $athlete = $app['stravaDL']->getAthlete($app['session']->get('key'));
    }
    catch(StravaDL\Exception\UnauthorizedException $e){
        $athlete = null;
    }
    catch(\Exception $e){
        $app->error(function (\Exception $e, $code) {
            return new Response('We are sorry, but something went terribly wrong.');
        });
    }
    return $app['twig']->render('index.twig', array('athlete' => $athlete));

})->bind('homepage');

$app->get('/auth', function(Request $request) use ($app){

    if($request->get('code')){
        $code = $request->get('code');
        $key = $app['stravaDL']->auth($code);

        $app['session']->set('key', $key);

        return $app->redirect('/');
    }
    else{
        return $app->redirect('/');
    }
});

$app->get('/deauth', function() use ($app){

    $key = $app['session']->get('key');
    $app['stravaDL']->deauth($key);
    $app['session']->set('key', null);
    return $app->redirect('/');
});


$app->run();
