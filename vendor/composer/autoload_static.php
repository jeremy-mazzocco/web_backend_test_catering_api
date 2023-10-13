<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit045d7a81c94a6168d73dc55af30477b8
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App',
        ),
    );

    public static $prefixesPsr0 = array (
        'B' => 
        array (
            'Bramus' => 
            array (
                0 => __DIR__ . '/..' . '/bramus/router/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit045d7a81c94a6168d73dc55af30477b8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit045d7a81c94a6168d73dc55af30477b8::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit045d7a81c94a6168d73dc55af30477b8::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit045d7a81c94a6168d73dc55af30477b8::$classMap;

        }, null, ClassLoader::class);
    }
}
