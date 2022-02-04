<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit97a6165eec06efbdd5a846e4baf79076
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Kapee\\PhpJwtHelper\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Kapee\\PhpJwtHelper\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit97a6165eec06efbdd5a846e4baf79076::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit97a6165eec06efbdd5a846e4baf79076::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit97a6165eec06efbdd5a846e4baf79076::$classMap;

        }, null, ClassLoader::class);
    }
}
