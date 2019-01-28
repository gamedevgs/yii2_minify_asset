# Yii2_minify_decrypt
Minify
1: Config 
Project/config/web.php add:
  
$config = [

    'aliases' => [
     
    ],
    'modules'             => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'components' => [
       
        ],

                
        
2: Custom and load folder to process minify and set patch to save
Project/modules/v1/controllers/BuildAssetsController.php

      //patch css
    public $css = 'asset/css';

    //patch js
    public $js = 'asset/js';
    
    //Group file if true
    public $GroupAsset = false;
    
    //return to patch source asset /var/www/html/($pachAsset)
    public $pachAsset = 'tto-html/projects';

    //return to patch string minify /var/www/html/($minifyPath)
    public $minifyPath = 'resources';
    
3:
Add source minify

    composer require rmrevin/yii2-minify-view

view more https://github.com/rmrevin/yii2-minify-view
