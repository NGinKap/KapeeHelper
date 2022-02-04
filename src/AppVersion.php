<?php

namespace PhpJwtHelper;

class AppVersion
{
    public static function get()
    {
        $app_env = config('app.env');
        $app_version = implode('.', [env('APP_MAJOR_VERSION', 1), env('APP_MINOR_VERSION', 0), env('APP_PATCH_VERSION', 0) ]);

        switch ($app_env) {
            case 'local':
            $additional_version = '-alpha';
            break;
            case 'development':
            $additional_version = '-beta';
            break;
            case 'staging':
            $additional_version = '-rc';
            break;
            default:
            $additional_version = null;
            break;
        }

        if (config('app.env') != 'production') {
            $commit_hash = trim(exec('git log --pretty="%h" -n1 HEAD'));

            $commit_date = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
            $commit_date->setTimezone(new \DateTimeZone('Asia/Jakarta'));
            $commit_date = $commit_date->format('Y-m-d H:i:s');
            if ($commit_hash) {
                $additional_version .= '+exp.sha.';
                $additional_version .= $commit_hash;
                $additional_version .= ' (' .  $commit_date . ')';
            }
        }

        return sprintf('v%s%s', $app_version, $additional_version);
    }
}
