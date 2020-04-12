let util = require('util');
const path = require('path');
let Client = require('ssh2-sftp-client');
let sftp = new Client();

(async () => {
        try {

                console.log(__dirname + '/../tmp/');

                const config = {
                        user: 'tab4lioz_laracsv',
                        password: 'Bravo2Alpha1!',
                        port: 22,
                        host: 'tab4lioz.beget.tech',
                };
                const src = path.resolve(__dirname + '/../tmp/');
                const dst = 'laracsv/tmp/';
                await sftp.connect(config);
                let dirs = [
                        'app',
                        'bootstrap',
                        //'config',
                        'database',
                        'public',
                        'resources',
                        'routes',
                        'storage',
                        'tests',
                        'tmp',
                ];

                dirs = [
                        'app',
                        'public',
                        'resources',
                        'routes',
                        'tests',
                ];

                for (let dir of dirs) {
                        let res = await sftp.uploadDir(dir, 'laracsv/' + dir);
                        console.log('deploying', dir);
                        console.log(util.inspect(dir));
                }

                let files = [
                        'config/auth.php',
                        'config/csvhandler.php',
                        '.gitignore',
                        'composer.json',
                        'package.json',
                        'README.md',
                        'server.php',
                        'webpack.mix.js',
                ];

                for (let file of files) {
                        let res = await sftp.put(file, 'laracsv/' + file);
                        console.log('deploying', file);
                        console.log(util.inspect(file));
                }





                // let exclude = [
                //         "tests/**",
                //         ".vscode/**",
                //         "vendor/**",
                //         "config/**",
                //         "laracsv.code-workspace",
                //         "tmp/**",
                //         "node_modules/**",
                //         "public/hot/**",
                //         "public/storage/**",
                //         "storage/*.key",
                //         "config/database.php",
                //         "util_dev/**",
                //         ".vscode/**",
                //         ".env",
                //         ".env.backup",
                //         ".phpunit.result.cache",
                //         "Homestead.json",
                //         "Homestead.yaml",
                //         "npm-debug.log",
                //         "yarn-error.log",
                // ];
                // sftp.on('upload', info => {
                //         console.log(util.inspect(info));
                // });
                // var minimatch = require("minimatch")

                // const fs = require('fs');
                // let pReadDiir = util.promisify(fs.readdir);
                // let rootList = await pReadDiir(__dirname + '/../');



                // topLoop: for (let item of rootList) {
                //         for (let excl of exclude) {
                //                 if (minimatch(excl, item)) {
                //                         console.log(item);
                //                         console.log(excl);
                //                 }

                //         }
                // }

                // dirs = ['tests'];

                // sftp.on('uploading', function (info) {
                //         console.log(info);

                // });

                // for (let dir of dirs) {
                //         let res = await sftp.uploadDir(dir, 'laracsv/' + dir);
                //         console.log('deploying', dir);
                //         console.log(util.inspect(dir));
                // }


                sftp.end();

        } catch (e) {
                console.log(e);
                sftp.end();
        }
})();


