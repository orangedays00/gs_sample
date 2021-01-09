<!DOCTYPE html>

<?php


// TIMEゾーンを東京に変更
date_default_timezone_set('Asia/Tokyo');





// 変数の初期化
$page_flag = 0;
$_CLEAN = array();
$_ERROR = array();

// サニタイズ
if ( !empty($_POST) ) {
  // var_dump($_POST);
  foreach( $_POST as $key => $value) {
    if($key == "post_password"){
      $once = htmlspecialchars( $value, ENT_QUOTES);
      $_CLEAN[$key] = hash("sha256",$once);
    }else{
      $_CLEAN[$key] = htmlspecialchars( $value, ENT_QUOTES);
      // var_dump($key);
    }
  }
  // var_dump($_CLEAN);
}

if (!empty($_CLEAN["btn_confirm"])){
  $_ERROR = validation($_CLEAN);

  if( empty($_ERROR) ) {
    $page_flag = 1;
    session_start();

    // 二重送信防止用トークンの発行
    $token = uniqid('', true);

    // トークンをセッション変数にセット
    $_SESSION['token'] = $token;
  }
} else if ( !empty($_CLEAN["btn_submit"])) {
  $page_flag = 2;
  session_start();

  // POSTされたトークンを取得
  $token = isset($_CLEAN['token']) ? $_CLEAN['token'] : "" ;

  // セッション変数のトークンを取得
  $session_token = isset($_SESSION['token']) ? $_SESSION['token'] : "";

  // セッション変数のトークンを削除
  unset($_SESSION['token']);


  // ここからデータベース登録

  // タイトル
  $title = $_POST["post_title"];

  // 名前
  $name = $_POST["post_name"];

  // メッセージ
  $message = $_POST["post_message"];

  // パス
  $password = $_POST["post_password"];

  // メッセージID
  $message_id = date(YmdHis);


  try {
    $pdo = new PDO('mysql:dbname=gs_db;charset=utf8;host=localhost','root','root');
  } catch (PDOException $e) {
    exit('DBConnectError:'.$e->getMessage());
  }

  $pdo->beginTransaction();
  // スレッド用テーブルへ登録
  $stmt = $pdo->prepare("INSERT INTO gs_thread_data(title,pass,closeFlg,disabledFlg,createTime,updateTime)VALUES(:title,:pass,'0','0',sysdate(), sysdate())");


  $stmt->bindValue(':title', $title, PDO::PARAM_STR);
  $stmt->bindValue(':pass', $password, PDO::PARAM_STR);

  $statusThread = $stmt->execute();


  // メッセージ用テーブルへの登録
  $stmt = $pdo->prepare("INSERT INTO gs_res_data(thread_id,message_id,userType,name,message,disabledFlg,createTime,updateTime)VALUES((SELECT MAX(thread_id) FROM gs_thread_data),:message_id,'1',:name,:message,'0',sysdate(), sysdate())");


  $stmt->bindValue(':message_id', $message_id, PDO::PARAM_STR);
  $stmt->bindValue(':name', $name, PDO::PARAM_STR);
  $stmt->bindValue(':message', $message, PDO::PARAM_STR);

  $statusMessage = $stmt->execute();

  $pdo->commit();

  if($statusThread == false || $statusMessage == false) {
    $error_sql = $stmt->errorInfo();
    exit("ErrorMessage:".$error_sql[2]);
  }

} else {
  $page_flag = 0;
}

function validation($_DATA){
  $_ERROR = array();

  // 名前のバリデーション
  if( empty($_DATA["post_name"]) ) {
      $_ERROR[] = "名前を入力してください。";
  } elseif (10 < mb_strlen($_DATA["post_name"])) {
      $_ERROR[] = "名前は10文字以内で入力してください";
  }

  // タイトルのバリデーション
  if( empty($_DATA["post_title"])) {
      $_ERROR[] = "タイトルを入力してください。";
  } elseif (30 < mb_strlen($_DATA["post_name"])) {
      $_ERROR[] = "タイトルは30文字以内で入力してください";
  }

  // コメントのバリデーション
  if( empty($_DATA["post_message"])) {
      $_ERROR[] = "コメントを入力してください。";
  }

  // パスワードのバリデーション
  if( empty($_DATA["post_password"])) {
      $_ERROR[] = "パスワードを入力してください。";
  }

  return $_ERROR;
}

$title = "新規スレッド作成";

?>

<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title><?php print $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://localhost/board/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://localhost/board/assets/css/myStyle.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="http://localhost/board/assets/js/autosize.min.js"></script>
    <script>
    $(function(){
      autosize($('textarea'));
    });
    </script>
  </head>
<body>
  <header>
    <h1 id="logo">
      <a href="/board/">スレッド式掲示板</a>
    </h1>
  </header>


  <?php if( $page_flag === 1 ): ?>

  <div class="container-fluid">
    <section class="container">
      <div class="row">
        <main class="col-md-9">
          <div class="new-form">
            <h2>確認画面</h2>
            <div></div>
          </div>
          <form method="POST" action="">
            <div class="form-group">
              <label>名前</label>
              <p class="form-control-plaintext"><?= $_CLEAN["post_name"]; ?></p>
            </div>
            <div class="form-group">
              <label>タイトル</label>
              <p class="form-control-plaintext"><?= $_CLEAN["post_title"]; ?></p>
            </div>
            <div class="form-group">
              <label>メッセージ</label>
              <p class="form-control-plaintext"><?= nl2br($_CLEAN["post_message"]) ?></p>
            </div>
            <div class="form-group">
              <label>パスワード</label>
              <p class="form-control-plaintext">******</p>
            </div>
            <div class="text-center">
              <input type="submit" name="btn_back" class="btn btn-primary btn-margin" value="戻る">
              <input type="submit" name="btn_submit" class="btn btn-primary btn-margin" value="投稿する">
            </div>
            <input type="hidden" name="post_name" value="<?= $_CLEAN["post_name"] ?>">
            <input type="hidden" name="post_title" value="<?= $_CLEAN["post_title"] ?>">
            <input type="hidden" name="post_message" value="<?= $_CLEAN["post_message"] ?>">
            <input type="hidden" name="post_password" value="<?= $_CLEAN["post_password"] ?>">
          </form>
        </main>
      </div>
    </section>
  </div>
  <?php elseif( $page_flag === 2 ): ?>

  <div class="container-fluid">
    <section class="container">
      <div class="row">
        <main class="col-md-9">
          <div class="new-form">
            <h2>投稿完了しました</h2>
          </div>
          <div class="back-home text-center">
            <a href="/board/">一覧に戻る</a>
          </div>
        </main>
      </div>
    </section>
  </div>

  <?php else: ?>
    <?php if ( !empty($_ERROR)): ?>
      <ul class="error-list">
      <?php foreach( $_ERROR as $value): ?>
        <li><?= $value; ?></li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="container-fluid">
      <section class="container">
        <div class="row">
          <main class="col-md-9">
            <div class="new-form">
              <h2>新規スレッド投稿フォーム</h2>
              <div>※全て入力してください</div>
            </div>
            <section id="inputForm">
            <form method="POST" action="">
              <div class="form-group">
                <label for="post_name">名前</label>
                <input type="text" id="post_name" class="form-control" name="post_name" value="<?php if ( !empty($_CLEAN["post_name"])){ echo $_CLEAN["post_name"]; } ?>">
                <label id="nameSupplement" class="supplement">10文字以内</label>
              </div>
              <div class="form-group">
                <label for="post_title">タイトル</label>
                <input type="text" id="post_title" class="form-control" name="post_title" value="<?php if ( !empty($_CLEAN["post_title"])){ echo $_CLEAN["post_title"]; } ?>">
                <label id="titleSupplement" class="supplement">30文字以内</label>
              </div>
              <div class="form-group">
                <label>メッセージ</label>
                <textarea name="post_message" id="post_message" class="form-control" cols="30"><?php if ( !empty($_CLEAN["post_message"])){ echo $_CLEAN["post_message"]; } ?></textarea>
                <label id="messageSupplement" class="supplement">5000文字以内</label>
              </div>
              <div class="form-group">
                <label for="post_password">パスワード</label>
                <input type="password" id="post_password" class="form-control"name="post_password" value="" minlength="4">
                <label id="passLabel" class="supplement">英数字4文字以上</label>
              </div>
              <div class="text-center">
                <input type="submit" name="btn_confirm" class="btn btn-primary" value="確認する">
              </div>
            </form>
            </section>
            <div class="back-home text-center">
              <a href="/board/">一覧に戻る</a>
            </div>
          </main>
        </div>
      </section>
    </div>
  <?php endif; ?>
  <footer id="footer">
  </footer>
</body>
</html>