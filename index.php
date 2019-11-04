<?php

require_once __DIR__ . '/vendor/autoload.php';

$inputString = file_get_contents('php://input');
error_log($inputString);

// HEROKU上の環境変数を読み込む
// アクセストークンの読み込み
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// チャンネルシークレットの読み込み
$bot = new \LINE\LINEBot($httpClient,['channelSecret' =>getenv('CHANNEL_SECRET')]);
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
$events = $bot->parseEventRequest(file_get_contents('php://input'),$signature);

unset($FLUG);
$FLUG="Text";

error_log("=====共通処理=====");

function RelatedUser($UserID)
{
	UserInsert($UserID);
}

function UserInsert($UserID)
{
	//初めての人はデータベースにUserIDを格納する
	error_log("=================================");
	error_log($FLUG);
	error_log("STEP1:データベースに接続をする");
	$pdo = new PDO('mysql:host='.getenv('SERVER').';dbname='.getenv('DATABASE').';charset=utf8',getenv('USERNAME'),getenv('PASSWORD'),array(PDO::ATTR_EMULATE_PREPARES => true));
	error_log("STEP2:SQL構文を作成する");
	$INSERT=$pdo ->prepare('INSERT INTO push(UserID) VALUES (:UserID)');
	error_log("STEP3:UserIDを設定する：".$UserID);
	$INSERT->bindParam(':UserID',$UserID,PDO::PARAM_STR);
	error_log("STEP4:SQLを実行する");
	$RESULT=$INSERT->execute();
	error_log("STEP5:SQLの実行結果");
	error_log($UserID."をデータベースに追加しました。");
	error_log("=================================");
}

function UserUpdate($profile_array)
{
	//初めての人はデータベースにUserIDを格納する
	error_log("=================================");
	error_log($FLUG);
	error_log("STEP1:データベースに接続をする");
	$pdo = new PDO('mysql:host='.getenv('SERVER').';dbname='.getenv('DATABASE').';charset=utf8',getenv('USERNAME'),getenv('PASSWORD'),array(PDO::ATTR_EMULATE_PREPARES => true));
	error_log("STEP2:SQL構文を作成する");
	$INSERT=$pdo ->prepare('UPDATE push SET displayName=:displayName ,pictureUrl=:pictureUrl WHERE UserID=:UserID');
	error_log("STEP3:各種変数を設定する");
	$INSERT->bindParam(':displayName',$profile_array['displayName'],PDO::PARAM_STR);
	$INSERT->bindParam(':pictureUrl',$profile_array['pictureUrl'],PDO::PARAM_STR);
	$INSERT->bindParam(':UserID',$profile_array['userId'],PDO::PARAM_STR);
	error_log("STEP4:SQLを実行する");
	$RESULT=$INSERT->execute();
	error_log("STEP5:SQLの実行結果");
	error_log($profile_array['userId']."のデータを更新しました。");
	error_log("=================================");
}

// ===== PROTOCOL =====
// ===== PROTOCOL =====

// ユーザープロフィールを取得する関数
function GetProfile($bot,$event)
{
	unset($UserID);
	$UserID=$event->getUserId();
	unset($response);
	$response = $bot->getProfile($UserID);
	if ($response->isSucceeded()) 
	{
		$profile = $response->getJSONDecodedBody();
		$displayName = $profile['displayName'];
		error_log("表示名：".$displayName);

		$userId = $profile['userId'];
		error_log("ユーザーID：".$userId);

		$pictureUrl = $profile['pictureUrl'];
		error_log("写真URL：".$pictureUrl);

		$statusMessage = $profile['statusMessage'];
		$profile_array = array("displayName"=>$displayName,"userId"=>$userId,"pictureUrl"=>$pictureUrl,"statusMessage"=>$statusMessage);
		UserUpdate($profile_array);
      		//$this->reply_message();
    }
}

// フォローしたユーザーをデータベースに格納する処理
function FollowProtocol($bot,$event)
{
	error_log("関数：フォロー処理を実行");
	try
	{
		$UserID=$event->getUserId();
		RelatedUser($UserID);

		// フォロー時に自動登録するために必要なAPIのエンドポイントを読み込む
		unset($url);
		$url=getenv('URL_AUTOREGIST').$UserID;
		error_log("アクセスするURL：".$url);

		//$ch = curl_init();
		// オプションを設定
		//curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

		// URLの情報を取得
		//$response =  curl_exec($ch);
	
		// 取得結果を表示
		//unset($res);
		//$res=json_decode($response);
		//error_log("[email]：".$res->email);
		//error_log("[password]：".$res->password);

		// セッションを終了
		//curl_close($ch);
		
		// フォローありがとうございますのメッセージを送信する
		//$MSG=getenv('MSG_FOLLOW');
		//$bot->pushMessage($UserID, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($MSG));
		
		// ログイン用のURLを返す
		//$MSG=getenv('URL_LOGIN').$UserID;
		//$bot->pushMessage($UserID, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($MSG));

		/*
		$actionBuilders=new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder('ログイン',$MSG);
		unset($TemplateBuilder);
		$title="ログイン";
		$text="ログイン";
		$imageUrl="https://d.line-scdn.net/n/line_lp/img/ogimage.png";
		$TemplateBuilder=new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder($title,$text,$imageUrl,array($actionBuilders));
		//debug($TemplateBuilder);
		unset($TemplateMessageBuilder);
		$TemplateMessageBuilder=new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($message,$TemplateBuilder);
		//debug($TemplateMessageBuilder);
		$result=$bot->pushMessage($UserID, $TemplateMessageBuilder);
		*/

	}
	catch (PDOException $e)
	{
                exit('データベース接続失敗。'.$e->getMessage());
                error_log($e->getMessage());
	}
}

// スタンプが送信されてきた際の処理
function StampProtocol($bot,$event)
{
	error_log("関数：スタンプ処理を実行");
        try
        {
		$UserID=$event->getUserId();
		$packageId=$event->getPackageId();
		$stickerId=$event->getStickerId();
		
		$MSG="パッケージID：".$packageId.", スタンプID:".$stickerId;
		$MSG="";
		//error_log("===============".$MSG."==================");

		// ウォーキングを判断する
		if(getenv('walking-id')==$packageId && getenv('walking-stampid')==$stickerId )
		{
				$type="ウォーキング";
		}

		if(getenv('drink-id')==$packageId && getenv('drink-stampid')==$stickerId )
		{
				$type="水分補給";
		}

		if(getenv('toilet-id')==$packageId && getenv('toilet-stampid')==$stickerId )
		{
				$type="トイレ";
		}

		$MSG=$type."の記録をしました";

		RelatedUser($UserID);
		//error_log($RESULT);
		//$bot->pushMessage($userId, new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder("1","2"));
		
		// データベースへ生活の記録を記載していく
		error_log("STEP1:データベースに接続をする");
		$pdo = new PDO('mysql:host='.getenv('SERVER').';dbname='.getenv('DATABASE').';charset=utf8',getenv('USERNAME'),getenv('PASSWORD'),array(PDO::ATTR_EMULATE_PREPARES => true));
		error_log("STEP2:SQL構文を作成する");
		$INSERT=$pdo ->prepare('INSERT INTO diary(UserID, type) VALUES (:UserID,:type)');
		error_log("STEP3:各種変数を設定する");
		$INSERT->bindParam(':UserID',$UserID,PDO::PARAM_STR);
		$INSERT->bindParam(':type',$type,PDO::PARAM_STR);
		error_log("STEP4:SQLを実行する");
		$RESULT=$INSERT->execute();
		error_log("STEP5:SQLの実行結果");
		error_log($UserID."のデータを追加しました。");


		// 本日何回目かを取得する
		error_log("STEP1:データベースに接続をする");
		$pdo = new PDO('mysql:host='.getenv('SERVER').';dbname='.getenv('DATABASE').';charset=utf8',getenv('USERNAME'),getenv('PASSWORD'),array(PDO::ATTR_EMULATE_PREPARES => true));
		error_log("STEP2:SQL構文を作成する");
		$SELECT=$pdo ->prepare('SELECT count(id) FROM diary WHERE type=:type AND UserID=:UserID');
		error_log("STEP3:各種変数を設定する");
		$SELECT->bindParam(':type',$type,PDO::PARAM_STR);
		$SELECT->bindParam(':UserID',$UserID,PDO::PARAM_STR);
		error_log("STEP4:SQLを実行する");
		$SELECT->execute();
		$RESULTS=$SELECT->fetchAll();
		foreach($RESULTS as $A)
		{
			error_log("STEP5:SQLの実行結果");
			error_log($type."：".$A['count(id)']."でした");
		}

		$bot->pushMessage($UserID, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($MSG));

       }
       catch (PDOException $e)
       {
                exit('データベース接続失敗。'.$e->getMessage());
                error_log($e->getMessage());
       }
}


// ===== PROTOCOL =====

error_log("STEP1:データベースの接続");

try
{
	/*
	error_log("データベース接続開始");
	error_log("データベースサーバー：".getenv('SERVER'));
	error_log("データベース：".getenv('DATABASE'));
	error_log("データベースユーザー名：".getenv('USERNAME'));
	error_log("データベースパスワード：".getenv('PASSWORD'));
	*/
	$SQL='mysql:host='.getenv('SERVER').';dbname='.getenv('DATABASE').';charset=utf8';
	//error_log("SQL：".$SQL);

	$pdo = new PDO('mysql:host='.getenv('SERVER').';dbname='.getenv('DATABASE').';charset=utf8',getenv('USERNAME'),getenv('PASSWORD'),array(PDO::ATTR_EMULATE_PREPARES => false));
	//error_log("データベース接続完了");
}
catch (PDOException $e)
{
	exit('データベース接続失敗。'.$e->getMessage());
        error_log($e->getMessage());
}

error_log(">>>STEP1:データベースの接続の終了");

// ===== ここからイベント処理 =====

error_log("STEP2:LINEのWebhookから受け取ったイベント情報の処理を開始");

foreach($events as $event)
{
	$UserID=$event->getUserId();
	error_log("ユーザーID：".$UserID);
	$TYPE=$event->getType();
	error_log("タイプ:".$TYPE);
	// フォロー処理
	if(($event instanceof LINE\LINEBot\Event\FollowEvent))
	{
		FollowProtocol($bot,$event);
		GetProfile($bot,$event);
	}
	// スタンプの処理
	if(($event instanceof LINE\LINEBot\Event\MessageEvent\StickerMessage))
	{
		StampProtocol($bot,$event);
	}
}

?>
