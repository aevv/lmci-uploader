<?php
  class PushStatus {
    // TODO: real json responses
    public static $Success = 1;
    public static $UnknownFailure = -1;
    public static $InvalidKey = -2;
    public static $NoUpload = -3;
    public static $BadKey = -4;
    public static $ExpiredKey = -5;
  }

  require_once("../../private/data-access/pgsql.php");
  require_once("../../private/secrets.php");

  $_pgConnection = new PGDataAccess(Secrets::$Host, Secrets::$Username, Secrets::$Password, Secrets::$Database);
  $_pgConnection->Connect();

  Process($_pgConnection);

  function Process($_connection)  {
    $key = GetAPIKey();
    ValidateKey($_connection, $key);
    $user = GetUserByKey($_connection, $key);

    $image = GetValidImage($_connection, $user);
  }

  function GetAPIKey()  {
    if (!isset($_POST['key']))
    {
      echo PushStatus::$InvalidKey;
      exit();
    }

    return pg_escape_string ($_POST['key']);
  }

  function ValidateKey($_connection, $key) {
    $key = $_connection->QueryParams("SELECT expire FROM lmci_key WHERE key = $1", array($key))
      ->GetNext();

    if (!$key) {
      echo PushStatus::$InvalidKey;
      exit();
    }

    $expire = $key[0];
    if ($expire < date("Y-m-d H:i:s")) {
      echo PushStatus::$ExpiredKey;
      exit();
    }
  }

  function GetValidImage($_connection, $user) {
    if (!isset($_FILES["upload"])) {
      echo PushStatus::$NoUpload;
      exit();
    }

    $file = pg_escape_string($_FILES["upload"]["name"]);
    // TODO: Redis
    $id = $_connection->Query("INSERT INTO LMCI_UPLOAD (uploaded, name, lmci_user)
      values (CURRENT_TIMESTAMP, '$file', '$user') RETURNING id")->GetNext()[0];

    $base64 = "data:image/png;base64," . base64_encode(file_get_contents($_FILES["upload"]["tmp_name"]));

    $_connection->Execute(
    "INSERT INTO LMCI_BLOB (lmci_upload, data) VALUES ($1, $2)", array($id, $base64));

    echo PushStatus::$Success . "," . $id;
  }

  function GetUserByKey($_connection, $key)  {
    $user = $_connection->Query("SELECT * FROM lmci_user u
      INNER JOIN lmci_key k on U.id = k.lmci_user WHERE KEY = '$key'")->GetNext();

    if ($user) {
      return $user[0];
    }

    echo PushStatus::$BadKey;
    exit();
  }
?>
