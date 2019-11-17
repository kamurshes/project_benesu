<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Shell;
// テーブルにアクセスするためのレジストリの使用
use Cake\ORM\TableRegistry;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;
use Psy\Shell as PsyShell;

/**
 * Simple console wrapper around Psy\Shell.
 */
class LinePushShell extends Shell
{

    /**
     * Start the shell and interactive console.
     *
     * @return int|null
     */
    public function main()
    {

	// シナリオを取得する
        $ScenarioTable = TableRegistry::get('scenarios');
	$Scenario=$ScenarioTable->find('all')->toArray();

	// pushテーブルに接続する
        $Table = TableRegistry::get('push');
	$Push=$Table->find('all')->toArray();
	foreach($Push as $a)
	{
		$datetime1=new \DateTime($a['Date']->i18nFormat('YYYY/MM/dd HH:mm:ss'));
		$datetime2=new \DateTime(date("Y/m/d H:i:s"));
		$interval = $datetime1->diff($datetime2);
		//debug($interval);
		try
		{
			//debug($a['Flug']);
			if($a['Flug']==$interval->d-1  && isset($Scenario[$interval->d-1]['text']))
			{
				self::fLinePushRawText($a['UserID'],$Scenario[$interval->d-1]['text']);
				$UP=$Table->find('all')->where(['UserID'=>$a['UserID']])->first();
				$UP->Flug=$interval->d;
				//debug($UP);
				if($Table->save($UP))debug("フラグ情報を更新しました");
			}
		}
		catch(Exception $e)
		{
			debug($e);
		}
			
	}

    }

    public static function fGetSitesInfo()
    {
	unset($SitesInfoTable);
        $SitesInfoTable = TableRegistry::get('SitesInfo');
	unset($SitesInfo);
	$SitesInfo=$SitesInfoTable->find()->first()->toArray();
	return($SitesInfo);
    }

    public static function fLinePushRawText($userId,$text)
    {
	unset($SitesInfo);
	$SitesInfo=self::fGetSitesInfo();
	debug("LINEボット");
	unset($httpClient);
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($SitesInfo['Access_Token']);
	unset($bot);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $SitesInfo['Channel_Secret']]);
	unset($message);
	$message="PUSH通知メッセージ";
	// メッセージをユーザーID宛にプッシュ
	//$title="タイトル";
	// ドメインならびにプロトコルを取得する
	//$userId="U1b6d9a2966ed9ac581ae0d42e59aab7e";
	$bot->pushMessage($userId, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
    }

}
