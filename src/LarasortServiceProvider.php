<?php

declare(strict_types=1);

namespace SDamian\Larasort;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Larasort - Service Provider.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
class LarasortServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publishables:

        $this->publishes([
            $this->getConfigPath() => config_path('larasort.php'),
        ], 'config');

        $this->publishes([
            $this->getCssPath() => public_path('vendor/larasort/css'),
        ], 'css');

        $this->publishes([
            $this->getImagesPath() => public_path('vendor/larasort/images'),
        ], 'images');

        // Blade Directives:

        Blade::directive('sortableUrl', function ($expression) {
            return "<?php echo \SDamian\Larasort\LarasortLink::getUrl({$expression}); ?>";
        });

        Blade::directive('sortableHref', function ($expression) {
            return "<?php echo \SDamian\Larasort\LarasortLink::getHref({$expression}); ?>";
        });

        Blade::directive('sortableIcon', function ($expression) {
            return "<?php echo \SDamian\Larasort\LarasortLink::getIcon({$expression}); ?>";
        });

        Blade::directive('sortableLink', function ($expression) {
            $ex = explode(', ', $expression);
            $label = $ex[1] ?? null;

            return "<?php echo \SDamian\Larasort\LarasortLink::getLink({$ex[0]}, {$label}); ?>";
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'larasort');
    }

    /**
     * Get the config file path of this package.
     */
    private function getConfigPath(): string
    {
        return __DIR__.'/../publish/config/larasort.php';
    }

    /**
     * Get the CSS directory path of this package.
     */
    private function getCssPath(): string
    {
        return __DIR__.'/../publish/css';
    }

    /**
     * Get the images directory path of this package.
     */
    private function getImagesPath(): string
    {
        return __DIR__.'/../publish/images';
    }
}
