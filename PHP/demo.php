<?php

include_once "wxBizDataCrypt.php";


$appid = 'wxbf4eea839f0b7aba';
$sessionKey = 'ZW1wSoMZfSmcTjvQmSLqhA==';

$encryptedData="7/erbK50R7CyZsrYsLCXyYZ2pVRnRIYYCHA/UOJDMkgPaGdfYqEi3JPwCmYSuClBWcRSr4t8dxrpQdpjaBYLSowkWHmbfKXaQWSysokGk9DtJoLeI/tCB+8s9UtJ8EZYaC8RcSeZ08wQvpcJjDK4Kcpw0yRD0jUvEzFlUmwKgKoJ45IOV93FgYbkjLA/q600BMkSDA0EqolGGiu2pjVkptPlxkf0pqTGUjGfqF664mC5Yg0FWM1TQBbIKMLuZkm82IhQTlcEzbyw8oCD8gYO9I378f372No7UWvqAQRHphykehqIhgPIdzgVXslXMF0GZIaBe5ZHvSjil3ROG7FoiKklmyp05lkRUOuiDy8ooa2gaPBA3dGUb0983XiDtlvP+1yzpKWl2swew+Y76k0muiCfIsUWR1VyoyAM10nRv4omxi9JZugZT/BDO8soW/8qVPN2Rf4+BeKWGscd00bxslFGV2nERe70kAtwQICbvN4=";

$iv = 'cQ72pnAHVw2jJTRcTpUEfg==';

$pc = new WXBizDataCrypt($appid, $sessionKey);
$errCode = $pc->decryptData($encryptedData, $iv, $data );

if ($errCode == 0) {
    print($data . "\n");
} else {
    print($errCode . "\n");
}
