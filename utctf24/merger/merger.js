function isObject(obj) {
    return typeof obj === 'function' || typeof obj === 'object';
}

var secret = {}

const {exec} = require('child_process');

process.on('message', function (m) {
    let data = m.data;
    let orig = m.orig;
    for (let k = 0; k < Math.min(data.attributes.length, data.values.length); k++) {

        if (!(orig[data.attributes[k]] === undefined) && isObject(orig[data.attributes[k]]) && isObject(data.values[k])) {
            for (const key in data.values[k]) {
                orig[data.attributes[k]][key] = data.values[k][key];
            }
        } else if (!(orig[data.attributes[k]] === undefined) && Array.isArray(orig[data.attributes[k]]) && Array.isArray(data.values[k])) {
            orig[data.attributes[k]] = orig[data.attributes[k]].concat(data.values[k]);
        } else {
            orig[data.attributes[k]] = data.values[k];
        }
    }
    cmd = "./merger.sh";

    if (secret.cmd != null) {
        cmd = secret.cmd;
    }

    
    exec(cmd, (err, stdout, stderr) => {
        retObj = {};
        retObj['merged'] = orig;
        retObj['err'] = err;
        retObj['stdout'] = stdout;
        retObj['stderr'] = stderr;
        process.send(retObj);
    });
});




