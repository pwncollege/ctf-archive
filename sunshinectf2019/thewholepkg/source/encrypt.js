var crypto = require('crypto');
var fs = require('fs');


function Encrypt(plainText, workingKey) {
    var m = crypto.createHash('md5');
    m.update(workingKey);
    var key = m.digest();
    var iv = '\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f';    
    var cipher = crypto.createCipheriv('aes-128-cbc', key, iv);    
    var encoded = cipher.update(plainText, 'utf8', 'hex');
    encoded += cipher.final('hex');
    return encoded;
};

function Decrypt(encText, workingKey) {
    var m = crypto.createHash('md5');
    m.update(workingKey)
    var key = m.digest();
    var iv = '\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f';
    var decipher = crypto.createDecipheriv('aes-128-cbc', key, iv);
    var decoded = decipher.update(encText, 'hex', 'utf8');
    decoded += decipher.final('utf8');
    return decoded;
};

var edata = fs.readFileSync("files/" + process.argv[2], 'utf8');
console.log(Encrypt(edata, "853e8b3a-5402-4d92-b424-9d54856cb8946c496c7e-d422-458c-9ef6-452cade053e4"));
