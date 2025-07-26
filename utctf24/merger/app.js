var express = require('express');
const cp = require('child_process');

var app = express();

const cookieParser = require('cookie-parser');
const session = require('express-session');
app.use(cookieParser());
app.use(session({
  secret: "jnius6npdiunasuivan4suvd82934",
  resave: false,
  saveUninitialized: true
}));


var userCount = 1;
var userCompanies = [[]];


app.set('view engine', 'ejs');
app.use(express.json());


app.get('/', function (req, res) {
  if (!req.session.init) {
    req.session.init = true;
    req.session.uid = userCount++;
    userCompanies[req.session.uid] = [];
  }
  res.render("index", {userID:req.session.uid});
})



app.post('/api/makeCompany', function (req, res) {
  if (!req.session.init) {
    res.end("invalid session");
    return;
  }
  let data = req.body;
  if (data.attributes === undefined || data.values === undefined ||
    !Array.isArray(data.attributes) || !Array.isArray(data.values)) {
    res.end('attributes and values are incorrectly set');
    return;
  }
  
  let cNum = userCompanies[req.session.uid].length;
  let cObj = new Object();
  for (let j = 0; j < Math.min(data.attributes.length, data.values.length); j++) {
    if (data.attributes[j] != '' && data.attributes[j] != null) {
      cObj[data.attributes[j]] = data.values[j];
    }
    
  }
  cObj.cid = cNum;
  userCompanies[req.session.uid][cNum] = cObj;

  res.end(cNum + "");
})


app.post('/api/absorbCompany/:cid', function (req, res) {
  if (!req.session.init) {
    res.end("invalid session");
    return;
  }
  try {
    var cid = parseInt(req.params.cid);
  } catch (e) {
    res.end('bad argument');
    return;
  }
  
  if (cid < 0 || cid >= userCompanies[req.session.uid].length) {
    res.end('not a valid company');
    return;
  }
  let data = req.body;
  if (data.attributes === undefined || data.values === undefined ||
    !Array.isArray(data.attributes) || !Array.isArray(data.values)) {
    res.end('attributes and values are incorrectly set');
    return;
  }
  let child = cp.fork("merger.js");
  child.on('message', function (m) {
    let cNum = userCompanies[req.session.uid].length;
    let message = "";
    if (m.merged != undefined) {
      m.merged.cid = cNum;
      userCompanies[req.session.uid][cNum] = m.merged;
    }
    if (m.err) {
      message += m.err;
    } else {
      message += m.stdout;
      message += m.stderr;
    }
    res.end(JSON.stringify(m));
    child.kill();
  })
  let dataObj = new Object()
  dataObj.data = data;
  dataObj.orig = userCompanies[req.session.uid][cid]
  child.send(dataObj);
  
})

app.get('/api/getAll', function (req, res) {
  if (!req.session.init) {
    res.end("invalid session");
  }
  let id = req.session.uid;
  res.end(JSON.stringify(userCompanies[id]));
  return;

})

var server = app.listen(8725, "127.0.0.1", function () {
  var host = server.address().address
  var port = server.address().port
  console.log("Example app listening at http://%s:%s", host, port)
});

