<?php
/**
 * Created by QuocHoi.
 * User: nam
 * Date: 19/10/2018
 * Time: 14:13
 */

namespace app\modules\v1\controllers;

use JSMin\JSMin;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;

class BuildAssetsController extends Controller
{
    //patch css
    public $css = 'asset/css';

    //patch js
    public $js = 'asset/js';

    public $jsarray = [];

    public $cssarray = [];

    public $jscontent = [];

    public $csscontent = [];

    public $GroupAsset = false;

    //return to patch source asset /var/www/html/($pachAsset)
    public $pachAsset = 'tto-html/projects';

    //return to patch string minify /var/www/html/($minifyPath)
    public $minifyPath = 'resources';

    public $api_name;

    public $verison;

    public $fileMode = 0664;

    public function Message($status)
    {
        $codes = Array(
            200 => 'Buid Asset Success!',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public function actionTest()
    {
        return $this->render('index.php');
    }

    public function actionIndex()
    {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $pass = '1';

        $user = '2';

       echo  $app_id = $request->post('app_id');

       echo $this->verison = $request->post('v');

       echo $token = $request->post('token');

        $this->api_name = '';

        echo $access = md5($app_id . $this->verison . date('h:i') . $pass . $user);

        switch ($app_id) {
            case "1":
                $this->api_name .= "web";
                break;
            case "2":
                $this->api_name .= "tnew";
                break;
            case "3":
                $this->api_name .= "pay";
                break;
            default:
                $this->api_name .= "";
        }
        if (isset($token) && $token == $access) {

            if (isset($app_id) && isset($this->verison)) {
                if ($this->api_name == '') {
                    $status = 404;
                    return array(
                        'status' => $status,
                        'data' => '',
                        'message' => 'Error, Name Project not isset!'
                    );
                } else {
                    if (isset($app_id) && isset($this->verison)) {
                        {
                            $this->Option();
                            $minifyPath = $this->getPatch();
                            if (is_dir($minifyPath)) {
                                $css_file = \yii\helpers\FileHelper::findFiles($minifyPath, ['only' => ['*.css']]);
                                $js_file = \yii\helpers\FileHelper::findFiles($minifyPath, ['only' => ['*.js']]);
                                $url2 = "http://assets.com";
                                $result_css = str_replace($this->GetpachHtml(), $url2, $css_file);
                                $result_js = str_replace($this->GetpachHtml(), $url2, $js_file);
                                return array(
                                    'status' => 200,
                                    'assets' =>
                                        [
                                            'css' => $result_css,
                                            'js' => $result_js
                                        ]
                                ,
                                    'message' => 'Buid Asset Success!'
                                );

                            } else {
                                $status = 404;
                                return array(
                                    'status' => $status,
                                    'data' => '',
                                    'message' => 'Error,Folder asset not Found, Could not load data form Resources',
                                );
                            }
                        }
                    }
                }
            }
        } else {
            $status = 404;
            return array(
                'status' => $status,
                'data' => '',
                'message' => 'Error, Access key token incorrect!'
            );
        }
    }

    public function Option()
    {
        ///var/www/html/tto-html/projects//asset/css
        $dir_css = $this->PatchAsset() . '/' . $this->api_name
            . '/' . $this->css;
        ///var/www/html/tto-html/projects//asset/css
        $dir_js = $this->PatchAsset() . '/' . $this->api_name
            . '/' . $this->js;

        if (true === $this->GroupAsset) {
            if (is_dir($dir_css) && is_dir($dir_js)) {
                $this->GroupfileJS();
                $this->GroupfileCss();
            } else {
                $status = 404;
                return array(
                    'status' => $status,
                    'data' => 'Error, Folder Asset Not Found!',
                    'message' => $this->Message($status)
                );
            }
        } else {
            if (is_dir($dir_css) && is_dir($dir_js)) {
                $this->AllMiniCssfile();
                $this->AllMiniJSfile();
            } else {
                $status = 404;
                return array(
                    'status' => $status,
                    'data' => 'Error, Folder Asset Not Found!',
                    'message' => $this->Message($status)
                );
            }
        }
    }

    public function GroupfileJS()
    {
        $jfcontent = "";
        $rootpatch = $this->PatchAsset() . DIRECTORY_SEPARATOR . $this->api_name
            . '/' . $this->js;

        $findfile = \yii\helpers\FileHelper::findFiles($rootpatch,
            ['only' => ['*.js']]);

        foreach ($findfile as $key => $jsFile) {

            $jfcontent .= file_get_contents($jsFile);
        }

        header("Content-type: application/javascript");

        //Lấy tên thư mục đằng sau tên Project
        $getdir = $this->SetDir($jsFile);
        //Tạo đường dẫn  vd :var/www/html/yii_minify/web/resources/tuoitrenews/cfff1524/pc/css/
        $patch = $this->getPatch() . $getdir;
        //Lấy tên của file
        $name_file = $this->Setnamefile($jsFile);
        //Xử lý minify nội dung file

        $this->Checkdir($patch);

        $create_file = $patch . DIRECTORY_SEPARATOR . $name_file . '.js';
        if (!file_exists($create_file)) {
            // $content = $this->ProcessMinijs($jfcontent);
            $content = JSMin::minify($jfcontent);
            file_put_contents($create_file, $content);
            if (false !== $this->fileMode) {
                @chmod($create_file, $this->fileMode);
            }
        }
    }

    public function PatchAsset()
    {
        $html_pach = $this->GetpachHtml();
        $pachAsset = $this->pachAsset;
        //return /var/www/html/tto-html/projects
        $asset = \Yii::getAlias($html_pach . '/' . $pachAsset);
        return $asset;
    }

    public function GetpachHtml()
    {
        //return /var/www/html
        Yii::setAlias('@html', realpath(dirname(__FILE__) . '/../../../../'));
        return Yii::getAlias('@html');
    }

    public function SetDir($cssFile)
    {
        //return string /var/www/html/tto-html/projects/api_name
        $patch2 = $this->PatchAsset() . DIRECTORY_SEPARATOR . $this->api_name;

        //return example "/asset/css/screen.css"
        $result = str_replace($patch2, '', $cssFile);

        //return example "/asset/css/"
        $dir_name = preg_replace('/(js\/)(.*\.js)|(css\/)(.*\.css)/', '$1$3',
            $result);

        //return example "/css/"
        $dir_asset = preg_replace('/\/asset/', '', $dir_name);

        return $dir_asset;

    }

    public function getPatch()
    {
        $minipatch = $this->minifyPath;
        //return var/www/html/resources
        $resource_dir = $this->GetpachHtml() . '/' . $minipatch;
        //return var/www/html/resources/api_name/hash
        $Path = $resource_dir . DIRECTORY_SEPARATOR . $this->api_name
            . DIRECTORY_SEPARATOR . $this->setHashName();
        return $Path;
    }

    public function setHashName()
    {
        $name = $this->hash($this->api_name);
        return $name;
    }

    public function hash($path)
    {
        $ver = $this->verison;

        return sprintf('%x', crc32($path . $ver));
    }

    public function Setnamefile($namefile)
    {
        //trả về tên file không chứa extensive
        return preg_replace('/(.*?)(js\/.*?)(.*?)(\.js)|(.*?)(css\/.*?)(.*?)(\.css)/',
            '$3$7', $namefile);
    }

    public function Checkdir($patch)
    {
        if (!file_exists($patch)) {

            FileHelper::createDirectory($patch);

        }
        if (!is_readable($patch)) {

            throw new Exception('Directory for compressed assets is not readable.');

        }

        if (!is_writable($patch)) {

            throw new Exception('Directory for compressed assets is not writable.');

        }

    }

    public function GroupfileCss()
    {
        $buffer = "";

        $rootpatch = $this->PatchAsset() . DIRECTORY_SEPARATOR . $this->api_name
            . '/' . $this->css;

        $findfile = \yii\helpers\FileHelper::findFiles($rootpatch,
            ['only' => ['*.css']]);

        foreach ($findfile as $key => $cssFile) {
            $buffer .= file_get_contents($cssFile);
        }

        header("Content-type: text/css");

        //Lấy tên thư mục đằng sau tên Project
        $getdir = $this->SetDir($cssFile);

        //Tạo đường dẫn  vd :var/www/html/yii_minify/web/resources/tuoitrenews/cfff1524/pc/css/
        $patch = $this->getPatch() . $getdir;

        //Lấy tên của file
        $name_file = $this->Setnamefile($cssFile);

        $this->Checkdir($patch);

        $create_file = $patch . DIRECTORY_SEPARATOR . $name_file . '.css';

        if (!file_exists($create_file)) {

            $csfile = \Minify_CSSmin::minify($buffer);
            //$csfile = $this->ProcessMinijs($buffer);

            file_put_contents($create_file, $csfile);

            if (false !== $this->fileMode) {
                @chmod($create_file, $this->fileMode);
            }
        }
    }

    public function AllMiniCssfile()
    {
        $patchdir = $this->PatchAsset() . DIRECTORY_SEPARATOR . $this->api_name
            . '/' . $this->css;

        $findfile = \yii\helpers\FileHelper::findFiles($patchdir,
            ['only' => ['*.css']]);

        foreach ($findfile as $cssFile) {

            $this->cssarray = file_get_contents($cssFile);

            // Set the correct MIME type, because Apache won't set it for us
            header("Content-type: text/css");

            //Lấy tên thư mục đằng sau tên Project
            $getdir = $this->SetDir($cssFile);
            //Tạo đường dẫn var/www/html/yii_minify/web/resources/tuoitrenews/cfff1524/pc/css/
            $patch = $this->getPatch() . $getdir;
            //Lấy tên của file
            $name_file = $this->Setnamefile($cssFile);

            $this->Checkdir($patch);

            $create_file = $patch . $name_file . '.css';

            if (!file_exists($create_file)) {

                $this->csscontent = \Minify_CSSmin::minify($this->cssarray);
                //$this->csscontent = $this->ProcessMinijs($this->cssarray);

                file_put_contents($create_file, $this->csscontent);

                if (false !== $this->fileMode) {
                    @chmod($create_file, $this->fileMode);
                }
            }
        }
    }

    public function AllMiniJSfile()
    {

        $patchdir = $this->PatchAsset() . DIRECTORY_SEPARATOR . $this->api_name
            . '/' . $this->js;

        $findfile = \yii\helpers\FileHelper::findFiles($patchdir,
            ['only' => ['*.js']]);

        foreach ($findfile as $key => $jsFile) {

            $this->jsarray = file_get_contents($jsFile);

            header("Content-type: application/javascript");

            //Lấy tên thư mục đằng sau tên Project
            $getdir = $this->SetDir($jsFile);

            //Tạo đường dẫn  vd :var/www/html/yii_minify/web/resources/tuoitrenews/cfff1524/pc/css/
            $patch = $this->getPatch() . $getdir;
            //Lấy tên của file
            $name_file = $this->Setnamefile($jsFile);
            //Xử lý minify nội dung file
//            $this->jscontent = $this->ProcessMinijs($this->jsarray);

            $this->Checkdir($patch);

            $create_file = $patch . $name_file . '.js';
            if (!file_exists($create_file)) {
                $this->jscontent = JSMin::minify($this->jsarray);
                file_put_contents($create_file, $this->jscontent);

                if (false !== $this->fileMode) {
                    @chmod($create_file, $this->fileMode);

                }

            }

        }
    }

    public function ProcessMinijs($input)
    {
        if (trim($input) === "") {

            return $input;
        }

        return preg_replace(
            [
                // Remove comment(s)
                /*         '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',*/

                // Remove white-space(s) outside the string and regex

                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                // Remove the last semicolon
                '#;+\}#',
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'

            ],
            [
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
            ],
            $input);
    }

    public function ProcessMinicss($input)
    {
        if (trim($input) === "") {

            return $input;

        }
        return preg_replace(
            [
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ],
            [
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ],
            $input);
    }

}

