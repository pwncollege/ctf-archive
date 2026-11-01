var readline = require('readline-sync');
var fs = require('fs');
var crypto = require('crypto');
var option

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



function list_dir(path) {
  files = fs.readdirSync(path);
  for (var i=0; i<files.length; i++) {
    console.log(path + files[i]);
  }
}

function read_file() {
  console.log("Which file would you like to read: ");
  files = fs.readdirSync(__dirname + "/files/");
  for (var i=0; i<files.length; i++) {
    console.log((i+1).toString()+". " + files[i]);
  }
  console.log("*************************************************************")
  console.log("************************READ FILES***************************")
  console.log("*************************************************************")
  var choice = readline.question("File number: ");
  if (choice > files.length) {
    return "Error: not a valid file";
  }
  else if (choice == '3') {
    return "You do not have permission to view this file"
  }

  var edata = fs.readFileSync(__dirname + "/files/"+files[parseInt(choice)-1], 'utf8');
  content = Decrypt(edata, "853e8b3a-5402-4d92-b424-9d54856cb8946c496c7e-d422-458c-9ef6-452cade053e4");
  return content
}

function print_welcome_prompt(){
  var asciiart = fs.readFileSync(__dirname + "/asciiart.txt", 'utf8');
  console.log(asciiart);
}
function print_main_prompt(){
  console.log("***********************Options: ***********************");
  console.log("1. List Files");
  console.log("2. Print File Contents");
  console.log("3. Exit");
}


print_welcome_prompt();

while (true) {
  print_main_prompt();
  option = readline.question("Selection : ");
  switch(option) {
    case '1':
      console.log("*************************************************************")
      console.log("**************************FILES******************************")
      console.log("*************************************************************")
      list_dir(__dirname + "/files/");
      console.log("*************************************************************")
      console.log("**************************FILES******************************")
      console.log("*************************************************************")
      break;
    case '2':
      console.log("*************************************************************")
      console.log("************************READ FILES***************************")
      console.log("*************************************************************")
      var content = read_file();
      console.log("File contents: ");
      console.log(content);
      break;
    case '3':
      process.exit();
      break;
    default:
      console.log("That is not a valid choice");
      break;

  }
}
