<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Project;
use Domain;

class Helper
{
    public static function autover($resource)
    {
        $time = filemtime(public_path() . $resource);
        $dot = strrpos($resource, '.');
        return asset(substr($resource, 0, $dot) . '.' . $time . substr($resource, $dot));
    }

    public static function route($name = null, $parameters = [], $query = true, $absolute = true, $domain = null)
    {
        $domain = $domain ?: Domain::current();
        $name = $domain . ($name ? '.' . $name : '');
        return app('url')->route($name, $parameters, $absolute) . ($query && Request::query() ? '?' . http_build_query(Request::query()) : '');
    }

    public static function url($domain = null, $name = null, $parameters = [], $query = true, $absolute = true)
    {
        return self::route($name, $parameters, $query, $absolute, $domain);
    }

    public static function arrayToTree($array, $parent = null)
    {
        $array = array_combine(array_column($array, 'id'), array_values($array));
        foreach ($array as $k => &$v) {
            if (isset($array[$v['parent']])) {
                $array[$v['parent']]['children'][$k] = &$v;
            }
            unset($v);
        }

        return array_filter($array, function ($v) use ($parent) {
            return $v['parent'] == $parent;
        });
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        $size = round($bytes, $precision);

        return $size ? $size . ' ' . $units[$pow] : $size;
    }

    public static function randomStr($length = 6, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*-=+;:?/,.')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function projects($request, $changeProject = false)
    {
        $projects = Project::selectRaw('TRIM(CONCAT(projects.name, IF(ISNULL(projects.location), "", ", "), COALESCE(projects.location, ""))) AS project, projects.id')->when(!Auth::user()->can('View All Projects'), function ($query) {
            return $query->leftJoin('project_user', 'project_user.project_id', '=', 'projects.id')->whereIn('project_user.project_id', Auth::user()->projects->pluck('id')->prepend(0))->where('project_user.user_id', Auth::user()->id);
        })->where('projects.status', 1)->pluck('project', 'projects.id')->prepend(trans('labels.allProjects'), 0);

        if ($changeProject) {
            if (count($projects) > 1) {
                $project = is_object($request) ? $request->input('project') : $request;
                if ($projects->has($project)) {
                    $project = $project;
                } elseif ($projects->has(session('project'))) {
                    $project = session('project');
                } else {
                    $project = $projects->keys()->first();
                }

                Session::put('project', $project);
            } else {
                Session::forget('project');
            }
        } else {
            if (!Session::has('project')) {
                Session::put('project', 0);
            }

            return $projects;
        }
    }

    public static function project()
    {
        $projects = session('project') ? collect(session('project')) : (Auth::user()->can('View All Projects') ? Project::pluck('id') : Auth::user()->projects->pluck('id'));

        return $projects->toArray();
    }

    public static function validateGSM7($string)
    {
        // ^[\x0A\x0C\x0D\x20-\x5F\x61-\x7E\xA0\xA1\xA3-\xA5\xA7\xBF\xC4-\xC6\xC9\xD1\xD6\xD8\xDC\xDF\xE0\xE4-\xE9\xEC\xF1\xF2\xF6\xF8\xF9\xFCΓΔΘΛΞΠΣΦΨΩ€]*$
        // ^[\x{20}-\x{7E}£¥èéùìòÇ\rØø\nÅåΔ_ΦΓΛΩΠΨΣΘΞ\x{1B}ÆæßÉ ¤¡ÄÖÑÜ§¿äöñüà\x{0C}€]*$
        return (preg_match('/^[A-Za-z0-9 \r\n@£$¥èéùìòÇØøÅå\x{0394}_\x{03A6}\x{0393}\x{039B}\x{03A9}\x{03A0}\x{03A8}\x{03A3}\x{0398}\x{039E}ÆæßÉ!"#$%&amp;\'()*+,\-.\/:;&lt;=&gt;?¡ÄÖÑÜ§¿äöñüà^{}\\\[~\]|\x{20AC}]*$/u', $string));

        /*-
        * ^[A-Za-z0-9 \r\n@£$¥èéùìòÇØøÅå\u0394_\u03A6\u0393\u039B\u03A9\u03A0\u03A8\u03A3\u0398\u039EÆæßÉ!"#$%&amp;'()*+,\-./:;&lt;=&gt;?¡ÄÖÑÜ§¿äöñüà^{}\\\[~\]|\u20AC]*$
        *
        * Assert position at the beginning of the string «^»
        * Match a single character present in the list below «[A-Za-z0-9 \r\n@£$¥èéùìòÇØøÅå\u0394_\u03A6\u0393\u039B\u03A9\u03A0\u03A8\u03A3\u0398\u039EÆæßÉ!"#$%&amp;'()*+,\-./:;&lt;=&gt;?¡ÄÖÑÜ§¿äöñüà^{}\\\[~\]|\u20AC]*»
        *    Between zero and unlimited times, as many times as possible, giving back as needed (greedy) «*»
        *    A character in the range between "A" and "Z" «A-Z»
        *    A character in the range between "a" and "z" «a-z»
        *    A character in the range between "0" and "9" «0-9»
        *    The character " " « »
        *    A carriage return character «\r»
        *    A line feed character «\n»
        *    One of the characters "@£$¥èéùìòÇØøÅå" «@£$¥èéùìòÇØøÅå»
        *    Unicode character U+0394 «\u0394», Greek capital Delta
        *    The character "_" «_»
        *    Unicode character U+03A6 «\u03A6», Greek capital Phi
        *    Unicode character U+0393 «\u0393», Greek capital Gamma
        *    Unicode character U+039B «\u039B», Greek capital Lambda
        *    Unicode character U+03A9 «\u03A9», Greek capital Omega
        *    Unicode character U+03A0 «\u03A0», Greek capital Pi
        *    Unicode character U+03A8 «\u03A8», Greek capital Psi
        *    Unicode character U+03A3 «\u03A3», Greek capital Sigma
        *    Unicode character U+0398 «\u0398», Greek capital Theta
        *    Unicode character U+039E «\u039E», Greek capital Xi
        *    One of the characters "ÆæßÉ!"#$%&amp;'()*+," «ÆæßÉ!"#$%&amp;'()*+,»
        *    A - character «\-»
        *    One of the characters "./:;&lt;=&gt;?¡ÄÖÑÜ§¿äöñüà^{}" «./:;&lt;=&gt;?¡ÄÖÑÜ§¿äöñüà^{}»
        *    A \ character «\\»
        *    A [ character «\[»
        *    The character "~" «~»
        *    A ] character «\]»
        *    The character "|" «|»
        *    Unicode character U+20AC «\u20AC», Euro sign
        * Assert position at the end of the string (or before the line break at the end of the string, if any) «$»
        */
    }

    public static function inlineHtml($body, $template = null)
    {
        if ($template == 'www-portugal-golden-visa-pt' || $template == 'www-mespil-ie') {
            $body = preg_replace('/<h1>/', '<h1 style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;color:#b46e00!important;font-weight:bold;">', $body);
            $body = preg_replace('/<h2>/', '<h2 style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;color:#b46e00!important;font-weight:bold;">', $body);
            $body = preg_replace('/<h3>/', '<h3 style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;color:#b46e00!important;font-weight:bold;">', $body);
            $body = preg_replace('/<h4>/', '<h4 style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;color:#b46e00!important;font-weight:bold;">', $body);
            $body = preg_replace('/<h5>/', '<h5 style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;color:#b46e00!important;font-weight:bold;">', $body);
            $body = preg_replace('/<h6>/', '<h6 style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;color:#b46e00!important;font-weight:bold;">', $body);
            $body = preg_replace('/<p>/', '<p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;">', $body);
            $body = preg_replace('/<p class="text-left">/', '<p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;text-align:left;">', $body);
            $body = preg_replace('/<p class="text-right">/', '<p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;text-align:right;">', $body);
            $body = preg_replace('/<p class="text-center">/', '<p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;text-align:center;">', $body);
            $body = preg_replace('/<p class="text-justify">/', '<p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px!important;margin-bottom:0px!important;margin-right:0px!important;margin-left:0px!important;padding-top:7px;padding-bottom:7px;text-align:justify;">', $body);
            $body = preg_replace('/<strong>/', '<strong style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<u>/', '<u style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<s>/', '<s style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<sub>/', '<sub style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<sup>/', '<sup style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<em>/', '<em style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<span>/', '<span style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<span style="(.*)">/', '<span style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;$1">', $body);
            $body = preg_replace('/<div>/', '<div style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<br>/', '<br style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<blockquote>/', '<blockquote style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<ul>/', '<ul style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<ul style="(.*)">/', '<ul style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;$1">', $body);
            $body = preg_replace('/<ol>/', '<ol style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<ol style="(.*)">/', '<ol style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;$1">', $body);
            $body = preg_replace('/<li>/', '<li style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-bottom:5px!important;">', $body);
            $body = preg_replace('/<a (.*)?href="(.*)">/', '<a href="$2" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;font-weight:bold;text-decoration:none!important;color:#555555!important;border-style:none!important;padding-right:10px;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:#ffcf40;">', $body);
            $body = preg_replace('/<table (.*)?style="(.*)">/', '<table align="left" valign="middle" width="100%" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt!important;mso-table-rspace:0pt!important;border-spacing:0!important;border-collapse:separate;table-layout:fixed!important;margin-top:0!important;margin-bottom:0!important;margin-right:auto!important;margin-left:auto!important;$2;width:100%">', $body);
            $body = preg_replace('/<tbody>/', '<tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<tr>/', '<tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">', $body);
            $body = preg_replace('/<td>/', '<td align="left" class="content" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt!important;mso-table-rspace:0pt!important;font-family:helvetica,\'lucidagrande\',\'lucidasansunicode\',\'lucidasans\',verdana,sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:5px;padding-left:5px;text-align:left;line-height:23px!important;font-size:16px!important;border-style:solid;border-color:#eeeeee;border-width:1px;">', $body);
            $body = preg_replace('/<hr \/>/', '<table align="center" valign="middle" width="90%" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt!important;mso-table-rspace:0pt!important;border-spacing:0!important;border-collapse:collapse;table-layout:fixed!important;margin-top:0!important;margin-bottom:0!important;margin-right:auto!important;margin-left:auto!important;width:100%"><tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;"><td align="center" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt!important;mso-table-rspace:0pt!important;border-style:solid;border-color:#eeeeee;border-width:1px 0 0 0;border-bottom-width:0;background:none;height:1px;width:100%;margin:0px;padding-top:0px;padding-bottom:0px;"></td></tr></table>', $body);
        } else {
            $body = preg_replace('/<h1>/', '<h1 style="color:#3853a3!important;font-size:26px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:left;">', $body);
            $body = preg_replace('/<h1 class="text-center">/', '<h1 class="text-center" style="color:#3853a3!important;font-size:26px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:center;">', $body);
            $body = preg_replace('/<h2>/', '<h2 style="color:#3853a3!important;font-size:20px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:left;">', $body);
            $body = preg_replace('/<h2 class="text-center">/', '<h2 class="text-center" style="color:#3853a3!important;font-size:20px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:center;">', $body);
            $body = preg_replace('/<h3>/', '<h3 style="color:#3853a3!important;font-size:16px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:left;">', $body);
            $body = preg_replace('/<h3 class="text-center">/', '<h3 class="text-center" style="color:#3853a3!important;font-size:16px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:center;">', $body);
            $body = preg_replace('/<h4>/', '<h4 style="color:#3853a3!important;font-size:14px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:left;">', $body);
            $body = preg_replace('/<h4 class="text-center">/', '<h4 class="text-center" style="color:#3853a3!important;font-size:14px;line-height:1.2;margin-top:0;margin-bottom:.5rem;text-align:center;">', $body);
            $body = preg_replace('/<p class="text-center">/', '<p class="text-center" style="text-align:center;">', $body);
            $body = preg_replace('/<p class="text-right">/', '<p class="text-right" style="text-align:right;">', $body);
        }

        return $body;
    }

    public static function getReplyImages($template, $preview = false)
    {
        $images = [];

        $domain = Domain::current();

        if ($template == 'www-portugal-golden-visa-pt') {
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-portugal-golden-visa-pt-logo.png', $preview), 'filename' => 'www-portugal-golden-visa-pt-logo.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/facebook.png', $preview), 'filename' => 'facebook.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/instagram.png', $preview), 'filename' => 'instagram.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/twitter.png', $preview), 'filename' => 'twitter.png']);
        } else { // $template == 'www-mespil-ie'
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-mespil-ie-logo.png', $preview), 'filename' => 'www-mespil-ie-logo.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/facebook.png', $preview), 'filename' => 'facebook.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/youtube.png', $preview), 'filename' => 'youtube.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/linkedin.png', $preview), 'filename' => 'linkedin.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/twitter.png', $preview), 'filename' => 'twitter.png']);
        }

        return $images;
    }

    public static function getTemplateImages($model, $preview = false)
    {
        $images = [];

        $domain = Domain::current();

        if ($model->template == 'www-pinehillsvilamoura-com' || $model->template == 'previsão-optima') {
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-pinehillsvilamoura-com-logo-header.png', $preview), 'filename' => 'www-pinehillsvilamoura-com-logo-header.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-pinehillsvilamoura-com-logo-footer.png', $preview), 'filename' => 'www-pinehillsvilamoura-com-logo-footer.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/facebook.png', $preview), 'filename' => 'facebook.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/youtube.png', $preview), 'filename' => 'youtube.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/instagram.png', $preview), 'filename' => 'instagram.png']);
        } elseif ($model->template == 'www-portugal-golden-visa-pt') {
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-portugal-golden-visa-pt-logo.png', $preview), 'filename' => 'www-portugal-golden-visa-pt-logo.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/facebook.png', $preview), 'filename' => 'facebook.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/instagram.png', $preview), 'filename' => 'instagram.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/twitter.png', $preview), 'filename' => 'twitter.png']);
        } else { // $model->template == 'www-mespil-ie'
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-mespil-ie-logo-header.png', $preview), 'filename' => 'www-mespil-ie-logo-header.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/www-mespil-ie-logo-footer.png', $preview), 'filename' => 'www-mespil-ie-logo-footer.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/facebook.png', $preview), 'filename' => 'facebook.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/youtube.png', $preview), 'filename' => 'youtube.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/linkedin.png', $preview), 'filename' => 'linkedin.png']);
            array_push($images, ['filePath' => self::getImageFilePath('/img/' . $domain . '/newsletters/twitter.png', $preview), 'filename' => 'twitter.png']);
        }

        return $images;
    }

    public static function getImageFilePath($path, $preview)
    {
        return $preview ? Helper::autover($path) : public_path($path);
    }

    public static function htmlEscapeAndLinkUrls($text)
    {
        $rexScheme = 'https?://';
        // $rexScheme = "$rexScheme|ftp://"; // Uncomment this line to allow FTP addresses.
        $rexDomain = '(?:[-a-zA-Z0-9\x7f-\xff]{1,63}\.)+[a-zA-Z\x7f-\xff][-a-zA-Z0-9\x7f-\xff]{1,62}';
        $rexIp = '(?:[1-9][0-9]{0,2}\.|0\.){3}(?:[1-9][0-9]{0,2}|0)';
        $rexPort = '(:[0-9]{1,5})?';
        $rexPath = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
        $rexQuery = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
        $rexFragment = '(#[!$-/0-9?:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
        $rexUsername = '[^]\\\\\x00-\x20\"(),:-<>[\x7f-\xff]{1,64}';
        $rexPassword = $rexUsername; // allow the same characters as in the username
        $rexUrl = "($rexScheme)?(?:($rexUsername)(:$rexPassword)?@)?($rexDomain|$rexIp)($rexPort$rexPath$rexQuery$rexFragment)";
        $rexTrailPunct = "[)'?.!,;:]"; // valid URL characters which are not part of the URL if they appear at the very end
        $rexNonUrl = "[^-_#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]"; // characters that should never appear in a URL
        $rexUrlLinker = "{\\b$rexUrl(?=$rexTrailPunct*($rexNonUrl|$))}";
        // $rexUrlLinker .= 'i'; // Uncomment this line to allow uppercase URL schemes (e.g. "HTTP://google.com").
        $validTlds = array_fill_keys(explode(" ", ".ac .academy .accountants .actor .ad .ae .aero .af .ag .agency .ai .airforce .al .am .an .ao .aq .ar .archi .army .arpa .as .asia .associates .at .attorney .au .audio .autos .aw .ax .axa .az .ba .bar .bargains .bayern .bb .bd .be .beer .berlin .best .bf .bg .bh .bi .bid .bike .bio .biz .bj .black .blackfriday .blue .bm .bn .bo .boutique .br .bs .bt .build .builders .buzz .bv .bw .by .bz .ca .cab .camera .camp .capital .cards .care .career .careers .cash .cat .catering .cc .cd .center .ceo .cf .cg .ch .cheap .christmas .church .ci .citic .ck .cl .claims .cleaning .clinic .clothing .club .cm .cn .co .codes .coffee .college .cologne .com .community .company .computer .condos .construction .consulting .contractors .cooking .cool .coop .country .cr .credit .creditcard .cruises .cu .cv .cw .cx .cy .cz .dance .dating .de .degree .democrat .dental .dentist .desi .diamonds .digital .directory .discount .dj .dk .dm .dnp .do .domains .dz .ec .edu .education .ee .eg .email .engineer .engineering .enterprises .equipment .er .es .estate .et .eu .eus .events .exchange .expert .exposed .fail .farm .feedback .fi .finance .financial .fish .fishing .fitness .fj .fk .flights .florist .fm .fo .foo .foundation .fr .frogans .fund .furniture .futbol .ga .gal .gallery .gb .gd .ge .gf .gg .gh .gi .gift .gives .gl .glass .globo .gm .gmo .gn .gop .gov .gp .gq .gr .graphics .gratis .gripe .gs .gt .gu .guide .guitars .guru .gw .gy .hamburg .haus .hiphop .hiv .hk .hm .hn .holdings .holiday .homes .horse .host .house .hr .ht .hu .id .ie .il .im .immobilien .in .industries .info .ink .institute .insure .int .international .investments .io .iq .ir .is .it .je .jetzt .jm .jo .jobs .jp .juegos .kaufen .ke .kg .kh .ki .kim .kitchen .kiwi .km .kn .koeln .kp .kr .kred .kw .ky .kz .la .land .lawyer .lb .lc .lease .li .life .lighting .limited .limo .link .lk .loans .london .lr .ls .lt .lu .luxe .luxury .lv .ly .ma .maison .management .mango .market .marketing .mc .md .me .media .meet .menu .mg .mh .miami .mil .mk .ml .mm .mn .mo .mobi .moda .moe .monash .mortgage .moscow .motorcycles .mp .mq .mr .ms .mt .mu .museum .mv .mw .mx .my .mz .na .nagoya .name .navy .nc .ne .net .neustar .nf .ng .nhk .ni .ninja .nl .no .np .nr .nu .nyc .nz .okinawa .om .onl .org .pa .paris .partners .parts .pe .pf .pg .ph .photo .photography .photos .pics .pictures .pink .pk .pl .plumbing .pm .pn .post .pr .press .pro .productions .properties .ps .pt .pub .pw .py .qa .qpon .quebec .re .recipes .red .rehab .reise .reisen .ren .rentals .repair .report .republican .rest .reviews .rich .rio .ro .rocks .rodeo .rs .ru .ruhr .rw .ryukyu .sa .saarland .sb .sc .schule .sd .se .services .sexy .sg .sh .shiksha .shoes .si .singles .sj .sk .sl .sm .sn .so .social .software .sohu .solar .solutions .soy .space .sr .st .su .supplies .supply .support .surgery .sv .sx .sy .systems .sz .tattoo .tax .tc .td .technology .tel .tf .tg .th .tienda .tips .tirol .tj .tk .tl .tm .tn .to .today .tokyo .tools .town .toys .tp .tr .trade .training .travel .tt .tv .tw .tz .ua .ug .uk .university .uno .us .uy .uz .va .vacations .vc .ve .vegas .ventures .versicherung .vet .vg .vi .viajes .villas .vision .vn .vodka .vote .voting .voto .voyage .vu .wang .watch .webcam .website .wed .wf .wien .wiki .works .ws .wtc .wtf .xn--3bst00m .xn--3ds443g .xn--3e0b707e .xn--45brj9c .xn--4gbrim .xn--55qw42g .xn--55qx5d .xn--6frz82g .xn--6qq986b3xl .xn--80adxhks .xn--80ao21a .xn--80asehdb .xn--80aswg .xn--90a3ac .xn--c1avg .xn--cg4bki .xn--clchc0ea0b2g2a9gcd .xn--czr694b .xn--czru2d .xn--d1acj3b .xn--fiq228c5hs .xn--fiq64b .xn--fiqs8s .xn--fiqz9s .xn--fpcrj9c3d .xn--fzc2c9e2c .xn--gecrj9c .xn--h2brj9c .xn--i1b6b1a6a2e .xn--io0a7i .xn--j1amh .xn--j6w193g .xn--kprw13d .xn--kpry57d .xn--l1acc .xn--lgbbat1ad8j .xn--mgb9awbf .xn--mgba3a4f16a .xn--mgbaam7a8h .xn--mgbab2bd .xn--mgbayh7gpa .xn--mgbbh1a71e .xn--mgbc0a9azcg .xn--mgberp4a5d4ar .xn--mgbx4cd0ab .xn--ngbc5azd .xn--nqv7f .xn--nqv7fs00ema .xn--o3cw4h .xn--ogbpf8fl .xn--p1ai .xn--pgbs0dh .xn--q9jyb4c .xn--rhqv96g .xn--s9brj9c .xn--ses554g .xn--unup4y .xn--wgbh1c .xn--wgbl6a .xn--xkc2dl3a5ee0h .xn--xkc2al3hye2a .xn--yfro4i67o .xn--ygbi2ammx .xn--zfr164b .xxx .xyz .yachts .ye .yokohama .yt .za .zm .zw .zone"), true);

        $html = '';

        $position = 0;
        $match = [];
        while (preg_match($rexUrlLinker, $text, $match, PREG_OFFSET_CAPTURE, $position)) {
            list($url, $urlPosition) = $match[0];

            $html .= htmlspecialchars(substr($text, $position, $urlPosition - $position));

            $scheme = $match[1][0];
            $username = $match[2][0];
            $password = $match[3][0];
            $domain = $match[4][0];
            $afterDomain = $match[5][0];
            $port = $match[6][0];
            $path = $match[7][0];

            $tld = strtolower(strrchr($domain, '.'));
            if (preg_match('{^\.[0-9]{1,3}$}', $tld) || isset($validTlds[$tld])) {
                if (!$scheme && $password) {
                    $html .= htmlspecialchars($username);
                    $position = $urlPosition + strlen($username);
                    continue;
                }

                if (!$scheme && $username && !$password && !$afterDomain) {
                    $completeUrl = "mailto:$url";
                    $linkText = $url;
                } else {
                    $completeUrl = $scheme ? $url : "http://$url";
                    $linkText = "$domain$port$path";
                }

                $linkHtml = '<a target="_blank" href="' . htmlspecialchars($completeUrl) . '">' . htmlspecialchars($linkText) . '</a>';
                // $linkHtml = str_replace('@', '&#64;', $linkHtml);

                $html .= $linkHtml;
            } else {
                $html .= htmlspecialchars($url);
            }

            $position = $urlPosition + strlen($url);
        }

        $html .= htmlspecialchars(substr($text, $position));

        return $html;
    }

    public static function linkUrlsInTrustedHtml($html)
    {
        $reMarkup = '{</?([a-z]+)([^"\'>]|"[^"]*"|\'[^\']*\')*>|&#?[a-zA-Z0-9]+;|$}';

        $insideAnchorTag = false;
        $position = 0;
        $result = '';

        while (true) {
            $match = [];
            preg_match($reMarkup, $html, $match, PREG_OFFSET_CAPTURE, $position);

            list($markup, $markupPosition) = $match[0];

            $text = substr($html, $position, $markupPosition - $position);

            if (!$insideAnchorTag) {
                $text = self::htmlEscapeAndLinkUrls($text);
            }

            $result .= $text;

            if ($markup === '') {
                break;
            }

            if ($markup[0] !== '&' && $match[1][0] === 'a') {
                $insideAnchorTag = ($markup[1] !== '/');
            }

            $result .= $markup;

            $position = $markupPosition + strlen($markup);
        }

        return $result;
    }
}
