function google_inject_object_in_div(id) {
  // only do this for IE
  if (navigator.userAgent.indexOf("MSIE ") <= 0) {
    return;
  }
  // make sure we have a div
  var div = document.getElementById(id);
  if (!div) {
    return;
  }
  // make sure it has at least 1 object tag
  var objTags = div.getElementsByTagName("object");
  if (objTags.length == 0) {
    return;
  }

  // Save FlashVars and movie.
  // FlashVars needs to be saved since the value is not copied when
  // the content of the div tag is updated.
  // The movie param needs to be saved since some of real player
  // plugin (version 11) overwrites the param with an wrong value
  // when div is updated. Note that the bug was not reproduced with
  // a plugin downloaded on Auguest 10th, 2007.
  var savedFlashVars = [];
  var savedMovie = [];
  for (var j = 0; j < objTags.length; j++) {
    var params = objTags[j].getElementsByTagName("param");
    for (var i = 0; i < params.length; i++) {
      if (params[i].name.toLowerCase() == 'flashvars') {
        savedFlashVars[j] = params[i].value;
      } else if (params[i].name.toLowerCase() == 'movie') {
        savedMovie[j] = params[i].value;
      }
    }
  }

  div.innerHTML = div.innerHTML;

  objTags = div.getElementsByTagName("object");
  if (objTags.length == 0) {
    return;
  }

  for (var j = 0; j < objTags.length; j++) {
    var s = objTags[j].outerHTML;
    if (savedMovie[j]) {
      // restore the movie param.
      var re = /<param name="movie" value="">/ig;
      s = s.replace(re,
                    "<param name='movie' value='" + savedMovie[j] + "'>");
    }

    if (savedFlashVars[j]) {
      // restore FlashVars.
      var re = /<param name="FlashVars" value="">/ig;
      s = s.replace(re,
                    "<param name='FlashVars' value='" + savedFlashVars[j] + "'>");
    }
    objTags[j].outerHTML = s;
  }
}

google_inject_object_in_div("google_flash_div");
