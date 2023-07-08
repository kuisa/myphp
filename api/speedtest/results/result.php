<center>

<?php

// 显示测速结果
// 参数id不为空时，显示指定id的结果
//     然后显示近期测试的结果

require_once 'telemetry_db.php';

error_reporting(0);


$speedtest = getSpeedtestUserById($_GET['id']);

$pdo = getPdo();
if (!($pdo instanceof PDO)) {
        return false;
}

if (isObfuscationEnabled()) {
        $id = deobfuscateId($id);
}

if (is_array($speedtest)) {
	$dlMbps = $speedtest['dl'];
	$ulMbps = $speedtest['ul'];
	echo "您的IP地址是: ".$speedtest['ip'];
  	echo "<table border=1 cellspacing=0>";
  	echo "<tr><th>方向</th><th>测试结果</th><th>结果排行</th><th>建   议</th></tr>\n";

  	echo '<tr><td>下载</td><td align=right>';
	echo $dlMbps;
	echo " Mbps</td><td align=right>";

	try {
		$stmt = $pdo->prepare('select count(*) from speedtest_users');
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NUM);
	} catch(Exception $e) {
		exit;
	}
	$totalspeed=$row[0];
	try {
		$stmt = $pdo->prepare('select count(*) from speedtest_users where dl<= :dl');
		$stmt->bindValue(':dl', $dlMbps, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NUM);
	} catch(Exception $e) {
		exit;
	}
	$dlrank=$row[0];

	echo round($dlrank*100/$totalspeed,2);
	echo "%</td><td>";

  	if ( $dlMbps > 50 ) 
		echo "网速飞快, 恭喜你";
  	else if ( $dlMbps > 20 )
		echo "网速正常";
  	else echo "网速有点慢(如果您使用的无线网络, 网速稍慢也是正常的)";
  	echo "</td></tr>\n";


   	echo '<tr><td>上传</td><td align=right>';
	echo $ulMbps;
	echo ' Mbps</td><td align=right>';

        try {
                $stmt = $pdo->prepare('select count(*) from speedtest_users where ul<= :ul');
                $stmt->bindValue(':ul', $ulMbps, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_NUM);
        } catch(Exception $e) {
                exit;
        }
        $ulrank=$row[0];

        echo round($ulrank*100/$totalspeed,2);
        echo "%</td><td>";

 	if ( $ulMbps > 50 ) 
		echo "网速飞快, 恭喜你";
  	else if ( $ulMbps > 10 )
		echo "网速正常";
  	else echo "网速有点慢(如果您使用的无线网络, 网速稍慢也是正常的)";
  	echo "</td></tr></table><p>\n";
}
?>

<p>近期测速信息
<table border=1 cellspacing=0>
<tr> <th>序号</th> <th>IP</th> <th>时间</th> <th>下载速度</th> <th>上传速度</th> </tr>
<?php
	try {
		$stmt = $pdo->prepare('select ip, timestamp, dl, ul from speedtest_users order by timestamp desc limit 20');
		$stmt->execute();
		$i=0;
		while($r = $stmt->fetch(PDO::FETCH_NUM)) {
			$i++;
        		echo "<tr>";
        		echo "<td align=center>$i</td>";
        		echo "<td>$r[0]</td>";
        		echo "<td>$r[1]</td>";
        		echo "<td align=right>$r[2] Mbps</td>";
        		echo "<td align=right>$r[3] Mbps</td>";
        		echo "</tr>\n";
		}
	} catch(Exception $e) {
		exit;
	}
?>
</table>

<p>最近1周测速排行
<table border=1 cellspacing=0>
<tr> <th>序号</th> <th>IP</th> <th>时间</th> <th>下载速度</th> <th>上传速度</th> </tr>
<?php
	try {
		$stmt = $pdo->prepare('select ip, timestamp, dl, ul from speedtest_users where timestamp>date_sub(now(), interval 7 day) order by dl desc limit 20');
		$stmt->execute();
		$i=0;
		while($r = $stmt->fetch(PDO::FETCH_NUM)) {
			$i++;
        		echo "<tr>";
        		echo "<td align=center>$i</td>";
        		echo "<td>$r[0]</td>";
        		echo "<td>$r[1]</td>";
        		echo "<td align=right>$r[2] Mbps</td>";
        		echo "<td align=right>$r[3] Mbps</td>";
        		echo "</tr>\n";
		}
	} catch(Exception $e) {
		exit;
	}
?>

</table>

<p>测速排行
<table border=1 cellspacing=0>
<tr> <th>序号</th> <th>IP</th> <th>时间</th> <th>下载速度</th> <th>上传速度</th> </tr>
<?php
	try {
		$stmt = $pdo->prepare('select ip, timestamp, dl, ul from speedtest_users order by dl desc limit 20');
		$stmt->execute();
		$i=0;
		while($r = $stmt->fetch(PDO::FETCH_NUM)) {
			$i++;
        		echo "<tr>";
        		echo "<td align=center>$i</td>";
        		echo "<td>$r[0]</td>";
        		echo "<td>$r[1]</td>";
        		echo "<td align=right>$r[2] Mbps</td>";
        		echo "<td align=right>$r[3] Mbps</td>";
        		echo "</tr>\n";
		}
	} catch(Exception $e) {
		exit;
	}

	// 删除旧的记录
	$stmt = $pdo->prepare('delete from speedtest_users where timestamp<date_sub(now(), interval 365 day)');
	$stmt->execute();
?>
</table>
</center>
