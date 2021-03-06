<?php 

namespace App\Http\Controllers\Back;

use Barryvdh\Elfinder\ {
    Session\LaravelSession,
    Connector,
    ElfinderController as ElfinderControllerBase
};

class ElfinderController extends ElfinderControllerBase
{
    /**
     * Override parent method
     */
    public function showConnector()
    {
        $roots = $this->app->config->get('elfinder.roots', []);

        $dirs = (array) $this->app['config']->get('elfinder.dir', []);

        foreach ($dirs as $dir) {
            $roots[] = [
                'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                'path' => public_path($dir), // path to files (REQUIRED)
                'URL' => $dir, // URL to files (REQUIRED)
                'accessControl' => $this->app->config->get('elfinder.access') // filter callback (OPTIONAL)
            ];
        }

        if ($directory = auth()->user()->getFilesDirectory()) {
            foreach($roots as &$root) {
                $root['path'] .= '/' . $directory;
                $root['URL'] .= '/' . $directory;
            }
        }

        if (app()->bound('session.store')) {
            $sessionStore = app('session.store');
            $session = new LaravelSession($sessionStore);
        } else {
            $session = null;
        }

        $rootOptions = $this->app->config->get('elfinder.root_options', array());
        foreach ($roots as $key => $root) {
            $roots[$key] = array_merge($rootOptions, $root);
        }

        $opts = $this->app->config->get('elfinder.options', array());
        $opts = array_merge($opts, ['roots' => $roots, 'session' => $session]);

        $connector = new Connector(new \elFinder($opts));
        $connector->run();
        return $connector->getResponse();
    }
}
