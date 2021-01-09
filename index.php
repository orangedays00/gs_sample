<!DOCTYPE html>
<?php
  $webRoot = $_SERVER['DOCUMENT_ROOT'];

  include_once($webRoot . "/board/src/function.php");
  include_once($webRoot . "/board/post.php");


  $search = h($_POST["search"]);

  $searchList = getSearchList($search);

?>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>スレッド式掲示板</title>
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
      <div class="container-fluid">
        <section class="container">
          <div class="row">
            <main class="col-md-9">
              <section class="threadList">

                <h3>新しくスレッドを立てたい方は<a href="new/">こちら</a>から投稿してください。</h3>

                <div class="search-area">
                  <p class="search-label">絞り込み検索</p>
                  <form action="" method="POST" class="search-form">
                    <input type="text" name="search" id="search" placeholder="キーワードで返信メッセージを絞り込めます" class="search-keyword">
                    <input type="submit" name="submit" value="絞り込む" class="btn btn-primary search-btn">
                  </form>
                </div>
                <?php
                    if(!empty($searchList)) {
                      foreach($searchList as $list){
                        outThreadList($list);
                      }
                    }else{
                      /** スレッド情報を全件取得 */
                      $results = getFullThread();
                      /** 1件ずつスレッドを出力する */
                      foreach($results as $result){
                          outThreadList($result);
                      }
                    }
                ?>
                </section>
            </main>
          </div>
        </section>
      </div>
      <footer id="footer">
      </footer>
    </body>

</html>