var exec = require('child_process').exec;
var fs = require(`fs`)

if (fs.existsSync("./PocketMine-MP.phar")) exec("rm PocketMine-MP.phar")
exec("composer make-server")