<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit129afaa26b27008c6209baaf3f16756b
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Filebase\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Filebase\\' => 
        array (
            0 => __DIR__ . '/..' . '/tmarois/filebase/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit129afaa26b27008c6209baaf3f16756b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit129afaa26b27008c6209baaf3f16756b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}