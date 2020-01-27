<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitde3bb5994be8ffc335079981fab72599
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Printful\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Printful\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitde3bb5994be8ffc335079981fab72599::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitde3bb5994be8ffc335079981fab72599::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
