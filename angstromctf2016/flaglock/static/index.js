$("#password-form").submit(function(event) {
    var pass = $("#password").val();
    var salt = 'pqzr3rCj2GX5VFsfbwCzHMD9';

    var time = Math.round(Date.now()/1000);

    var pass_hash = sha256(salt+pass);
    var data = sha256(pass_hash+time.toString(16))+time.toString(16);

    $("#password").val('');
    $("#data").val(data);
});