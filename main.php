<?php

use Rowbot\URL\String\EncodeSet;
/*
Plugin Name:modal scroll img
Description:スクロールで広告がモーダルで出る
*/

// 「設定」にメニューを追加
add_action( 'admin_menu', function () {
  add_menu_page(
    'モーダル画像', // 設定画面のページタイトル.
    'モーダル画像', // 管理画面メニューに表示される名前.
    'manage_options',
    'my-original-menu', // メニューのスラッグ
    'my_original_menu_page' // この関数を呼び出す
  );
});


// メニューページの中身を作成
function my_original_menu_page() {
    // ここで4つのpost値が来てるかを判定 
    if(!empty($_POST['image_path']) 
        && !empty($_POST['top']) 
        && !empty($_POST['cat_id']) 
        && !empty($_POST['url']) 
    ){
        //ちゃんとサニタイズもする
        $post = [];
        foreach ($_POST as $key => $str) {
            if ($key != 'cat_id'){
                $crean_text = htmlspecialchars($str, ENT_QUOTES);
                $post[$key] = $crean_text;
            }else{
                $post[$key] = $str;
            }
        } 
        //改行があっても一行目だけにする
        $post['url'] = explode("\n", $post['url'])[0];

        // wp_optionsにアップサート 
        update_option('modal_scroll',$post);
        // 保存したらメッセージを出す
        echo '<div class="wrap" style="color:#4090ed;"> 保存しました。</div>';
    }

    // 今更新した値を取り出してみる
    // SELECT * FROM wp_options WHERE ID = 'modal_scroll' をアンシリアライズして配列として取得
    $modal_settings = get_option('modal_scroll');
    // var_dump($modal_settings);

    ?>
    <!-- こっちがインプット -->
    <?php require_once 'admin-input.php' ?>
    <!-- こっちがインプット -->

    <?php
}



// 記事に挿入(サイト側の方)
add_filter(
    'the_content',
    function ($content) {
        //設定されてる値をDBから取得
        $modal_settings = get_option('modal_scroll');

        //開いてるページのカテゴリIDを取得
        $categories = get_the_category();
        foreach ($categories as $category) {
            $now_category = $category->term_id; //カテゴリIDの出力;
        }
        // 投稿以外のページにはカテゴリが無いので
        if (isset($now_category, $modal_settings['cat_id']) && in_array($now_category, $modal_settings['cat_id'])){
            //プラグインフォルダのURLを取得(cssはurl参照なので)
            $plugin_url = plugin_dir_url( __FILE__ );
            $html = "<link rel='stylesheet' href='$plugin_url/style.css'>";
            
            // 別ファイルのhtmlを読み込む絶対パス必要 
            $html .= file_get_contents(
                WP_PLUGIN_DIR .'/modal-scroll-img/modal.html'
            );
            // 画像のurlはフルパスを入れる必要がある
            $html = sprintf(
                $html, 
                $modal_settings['url'], 
                $modal_settings['image_path'],
                $modal_settings['top'], 
            );
        }else{
            $html = '';
        }
        return $content . $html; // ← これが記事本文
}       // 対象カテゴリじゃなければ .$html はカラ文字
 , 10 ); 
// 他にあれば10番目に実行される