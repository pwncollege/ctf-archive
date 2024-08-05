function GetCookie(name) {
  var i  = 0;
  while (i < document.cookie.length) {
    var j = i + (name.length) + 1;
    if (document.cookie.substring(i,j) == name+"=") {
      var endstr = document.cookie.indexOf (";", j);
      if (endstr < j) endstr = document.cookie.length;
      return unescape(document.cookie.substring(j, endstr));
    }
    i = document.cookie.indexOf(" ", i) + 1;
    if (i == 0) break;
  }
  return null;
}
var hiddenblocks = new Array();
var blocks = GetCookie('hiddenblocks');
if (blocks != null) {
  var hidden = blocks.split(":");
  for (var loop = 0; loop < hidden.length; loop++) {
    var hiddenblock = hidden[loop];
    hiddenblocks[hiddenblock] = hiddenblock;
  }
}
function blockswitch(bid) {
  var bpe  = document.getElementById('pe'+bid);
  var bph  = document.getElementById('ph'+bid);
  var bico = document.getElementById('pic'+bid);
  if (bpe && bpe.style.display=="none") {
    if (bph) { bph.style.display="none"; }
    bpe.style.display="";
    hiddenblocks[bid] = null;
    bico.src = bico.src.replace("plus.", "minus.");
  } else {
    if (bph) { bph.style.display=""; }
    if (bpe) { bpe.style.display="none"; }
    hiddenblocks[bid] = bid;
    bico.src = bico.src.replace("minus.", "plus.");
  }
  var cookie = null;
  for (var q = 0; q < hiddenblocks.length; q++) {
    if (hiddenblocks[q] != null) {
      cookie = (cookie != null) ? (cookie+":"+hiddenblocks[q]) : hiddenblocks[q];
    }
  }
  if (cookie != null) {
    var exp = new Date();
    exp.setTime(exp.getTime() + (24 * 60 * 60 * 1000 * 365));
    var expstr = "; expires=" + exp.toGMTString();
    document.cookie = "hiddenblocks=" + escape(cookie) + expstr + "; path=/;"; // "domain=cpgnuke.com;";
  } else if (GetCookie("hiddenblocks")) {
    document.cookie = "hiddenblocks=:; expires = Thu, 01-Jan-70 00:00:01 GMT; path=/;";
  }
}

function simpleswitch(str) {
  var bpe  = document.getElementById(str);
  if (bpe && bpe.style.display=="none") {
    bpe.style.display="";
  } else {
    if (bpe) { bpe.style.display="none"; }
  }
}
